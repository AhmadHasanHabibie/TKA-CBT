<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Subtest;
use App\Services\PdfParserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PdfUploadController extends Controller
{
    protected PdfParserService $parserService;

    public function __construct(PdfParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Download the official TKA PDF template file.
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('app/template_soal_tka.pdf');

        // Always regenerate to ensure clean word-wrapping and prevent text truncation
        $this->generateTemplatePdfFile($filePath);

        return response()->download($filePath, 'template_soal_tka.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Generate the official template PDF with automatic word-wrapping to prevent cut-off text.
     */
    protected function generateTemplatePdfFile(string $outputPath): void
    {
        $rawLines = [
            "TEMPLATE SOAL UJIAN TKA (TES KOMPETENSI AKADEMIK)",
            "Petunjuk: Ikuti struktur penulisan di bawah secara konsisten.",
            "",
            "SOAL 1",
            "Sebuah segitiga memiliki alas 10 cm dan tinggi 6 cm. Berapakah luasnya?",
            "A. 20 cm2",
            "B. 30 cm2",
            "C. 40 cm2",
            "D. 50 cm2",
            "E. 60 cm2",
            "KUNCI: B",
            "PEMBAHASAN: Luas segitiga = (1/2) * alas * tinggi = (1/2) * 10 * 6 = 30 cm2.",
            "",
            "SOAL 2",
            "Manakah pernyataan berikut yang benar mengenai bilangan prima?",
            "A. 2 adalah satu-satunya bilangan prima genap",
            "B. Semua bilangan prima adalah bilangan ganjil",
            "C. 1 termasuk bilangan prima",
            "D. 7 adalah bilangan prima",
            "E. Semua bilangan ganjil adalah bilangan prima",
            "KUNCI: A, D",
            "PEMBAHASAN: Bilangan prima hanya memiliki 2 faktor (1 dan dirinya sendiri). 2 adalah satu-satunya prima genap (A benar), 7 adalah prima (D benar). Opsi B, C, E salah.",
            "",
            "SOAL 3",
            "Sebuah penelitian menunjukkan bahwa peningkatan suhu air laut berkorelasi dengan penurunan populasi terumbu karang di kawasan tersebut selama 10 tahun terakhir.",
            "PERNYATAAN:",
            "1. Suhu air laut yang meningkat berdampak negatif terhadap populasi terumbu karang.",
            "2. Populasi terumbu karang meningkat seiring naiknya suhu air laut.",
            "3. Penelitian tersebut dilakukan dalam kurun waktu satu dekade.",
            "KUNCI: S, TS, S",
            "PEMBAHASAN: Berdasarkan bacaan, korelasi antara suhu air laut dan penurunan populasi terumbu karang bersifat negatif (pernyataan 1 sesuai), sehingga pernyataan 2 yang menyatakan sebaliknya tidak sesuai. Penelitian disebutkan berlangsung 10 tahun/satu dekade (pernyataan 3 sesuai).",
        ];

        // Automatically wrap long lines at 78 characters so text stays well within page margins
        $lines = [];
        foreach ($rawLines as $line) {
            if (trim($line) === '') {
                $lines[] = '';
                continue;
            }

            $wrapped = wordwrap($line, 78, "\n", false);
            $parts = explode("\n", $wrapped);
            foreach ($parts as $p) {
                $lines[] = rtrim($p);
            }
        }

        $contentStream = "BT\n/F1 10 Tf\n45 745 Td\n13.5 TL\n";
        foreach ($lines as $line) {
            $safeLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $contentStream .= "({$safeLine}) '\n";
        }
        $contentStream .= "ET\n";

        $streamLength = strlen($contentStream);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        $offsets[1] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $offsets[2] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        $offsets[3] = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

        $offsets[4] = strlen($pdf);
        $pdf .= "4 0 obj\n<< /Length {$streamLength} >>\nstream\n{$contentStream}endstream\nendobj\n";

        $offsets[5] = strlen($pdf);
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        file_put_contents($outputPath, $pdf);
    }

    /**
     * Show the upload PDF form for a subtest.
     */
    public function showUpload(Subtest $subtest)
    {
        $existingCount = $subtest->questions()->count();

        return view('admin.subtests.upload', compact('subtest', 'existingCount'));
    }

    /**
     * Handle PDF parsing and store results in session staging.
     */
    public function parsePdf(Request $request, Subtest $subtest)
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'pdf_file.required' => 'File PDF wajib diunggah.',
            'pdf_file.mimes' => 'Format file harus berupa PDF (.pdf).',
            'pdf_file.max' => 'Ukuran file PDF maksimal 20 MB.',
        ]);

        try {
            $pdfFile = $request->file('pdf_file');
            $parsedQuestions = $this->parserService->parse($pdfFile->getRealPath(), $subtest->id);

            if (empty($parsedQuestions)) {
                return back()->with('error', 'File PDF tidak memuat blok soal dengan pola SOAL [nomor] yang valid.');
            }

            // Store in session for staging area
            session(["subtest_staging_{$subtest->id}" => $parsedQuestions]);

            $count = count($parsedQuestions);

            return redirect()->route('admin.subtests.preview', $subtest)
                ->with('success', "PDF berhasil di-parse! Ditemukan {$count} blok soal. Silakan tinjau dan edit sebelum mempublikasikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses file PDF: ' . $e->getMessage());
        }
    }

    /**
     * Display the Staging / Preview form for review and edits.
     */
    public function preview(Subtest $subtest)
    {
        $sessionKey = "subtest_staging_{$subtest->id}";
        $questions = session($sessionKey);

        if (!$questions) {
            return redirect()->route('admin.subtests.upload', $subtest)
                ->with('error', 'Belum ada data staging atau sesi preview telah kedaluwarsa. Silakan upload ulang file PDF.');
        }

        $errorCount = collect($questions)->where('has_error', true)->count();

        return view('admin.subtests.preview', compact('subtest', 'questions', 'errorCount'));
    }

    /**
     * Commit the staged questions into database in a transaction.
     */
    public function publish(Request $request, Subtest $subtest)
    {
        $sessionKey = "subtest_staging_{$subtest->id}";
        $rawSubmittedQuestions = $request->input('questions', []);

        if (empty($rawSubmittedQuestions)) {
            return back()->with('error', 'Tidak ada data soal untuk disimpan.');
        }

        // Pre-validate questions to ensure correct key configuration
        $questionIndex = 1;
        foreach ($rawSubmittedQuestions as $qData) {
            $type = in_array($qData['type'] ?? '', ['single', 'multiple', 'statement']) ? $qData['type'] : 'single';
            
            if ($type === 'statement') {
                $optionsData = $qData['options'] ?? [];
                if (count($optionsData) < 2) {
                    return back()->with('error', "Soal nomor {$questionIndex} bertipe Sesuai / Tidak Sesuai wajib memiliki minimal 2 butir pernyataan.")->withInput();
                }

                foreach ($optionsData as $stmtIdx => $opt) {
                    $stmtText = trim($opt['text'] ?? '');
                    if ($stmtText === '') {
                        return back()->with('error', "Pernyataan nomor " . ($stmtIdx + 1) . " pada soal nomor {$questionIndex} tidak boleh kosong.")->withInput();
                    }
                    if (!isset($opt['is_correct']) || $opt['is_correct'] === '') {
                        return back()->with('error', "Pernyataan nomor " . ($stmtIdx + 1) . " pada soal nomor {$questionIndex} belum memiliki kunci Sesuai / Tidak Sesuai.")->withInput();
                    }
                }
            } else {
                // Extract correct keys array for single or multiple
                $correctKeys = [];
                if ($type === 'multiple') {
                    $correctKeys = (array) ($qData['correct_keys'] ?? []);
                } else {
                    if (!empty($qData['correct_key'])) {
                        $correctKeys = [(string) $qData['correct_key']];
                    } elseif (!empty($qData['correct_keys'])) {
                        $correctKeys = array_slice((array) $qData['correct_keys'], 0, 1);
                    }
                }

                if ($type === 'single' && count($correctKeys) !== 1) {
                    return back()->with('error', "Soal nomor {$questionIndex} bertipe Pilihan Ganda biasa wajib memiliki tepat 1 kunci jawaban.")->withInput();
                }

                if ($type === 'multiple' && count($correctKeys) < 2) {
                    return back()->with('error', "Soal nomor {$questionIndex} bertipe Pilihan Ganda Kompleks (PGK) wajib memiliki minimal 2 kunci jawaban benar. Ubah tipe menjadi Pilihan Ganda biasa jika hanya memiliki 1 jawaban.")->withInput();
                }
            }

            $questionIndex++;
        }

        DB::beginTransaction();
        try {
            // Remove previous questions if any (replace behavior)
            $subtest->questions()->delete();

            $questionNumber = 1;
            foreach ($rawSubmittedQuestions as $key => $qData) {
                $questionText = trim($qData['text'] ?? '');
                $explanation = trim($qData['explanation'] ?? '');
                $type = in_array($qData['type'] ?? '', ['single', 'multiple', 'statement']) ? $qData['type'] : 'single';

                if (empty($questionText)) {
                    continue;
                }

                // Handle image (uploaded file or preserved staging image)
                $imagePath = null;
                $deleteImage = !empty($qData['delete_image']) && $qData['delete_image'] === '1';

                $uploadedFile = $request->file("questions.{$key}.image_file");
                if ($uploadedFile && $uploadedFile->isValid()) {
                    $ext = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
                    $filename = "img_q_{$subtest->id}_{$questionNumber}_" . time() . '_' . uniqid() . '.' . $ext;
                    $uploadedFile->storeAs('questions', $filename, 'public');
                    $imagePath = 'questions/' . $filename;
                } elseif (!$deleteImage && !empty($qData['existing_image'])) {
                    $existing = $qData['existing_image'];
                    if (str_starts_with($existing, 'staging_images/')) {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($existing)) {
                            $ext = pathinfo($existing, PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = "img_q_{$subtest->id}_{$questionNumber}_" . time() . '_' . uniqid() . '.' . $ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->copy($existing, 'questions/' . $filename);
                            $imagePath = 'questions/' . $filename;
                        }
                    } elseif (str_starts_with($existing, 'questions/')) {
                        $imagePath = $existing;
                    }
                }

                $question = Question::create([
                    'subtest_id' => $subtest->id,
                    'number' => $questionNumber++,
                    'type' => $type,
                    'text' => $questionText,
                    'image' => $imagePath,
                    'explanation' => !empty($explanation) ? $explanation : null,
                ]);

                $optionsData = $qData['options'] ?? [];

                if ($type === 'statement') {
                    // For statement questions: label is statement index "1", "2", ... and is_correct is Sesuai (true) or Tidak Sesuai (false)
                    $stmtNum = 1;
                    foreach ($optionsData as $opt) {
                        $text = trim($opt['text'] ?? '');
                        if ($text === '') {
                            continue;
                        }

                        $isCorrect = filter_var($opt['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN);

                        Option::create([
                            'question_id' => $question->id,
                            'label' => (string) $stmtNum++,
                            'text' => $text,
                            'is_correct' => $isCorrect,
                        ]);
                    }
                } else {
                    // Determine correct keys array
                    if ($type === 'multiple') {
                        $correctKeys = array_map('strtoupper', (array) ($qData['correct_keys'] ?? []));
                    } else {
                        $correctKey = !empty($qData['correct_key']) ? $qData['correct_key'] : ($qData['correct_keys'][0] ?? 'A');
                        $correctKeys = [strtoupper(trim($correctKey))];
                    }

                    foreach ($optionsData as $opt) {
                        $label = strtoupper(trim($opt['label'] ?? ''));
                        $text = trim($opt['text'] ?? '');

                        if (empty($label)) {
                            continue;
                        }

                        Option::create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'text' => $text,
                            'is_correct' => in_array($label, $correctKeys),
                        ]);
                    }
                }
            }

            // Sync total_questions count
            $subtest->updateQuestionCount();

            // Clear session staging
            session()->forget($sessionKey);

            DB::commit();

            return redirect()->route('admin.subtests.index')
                ->with('success', "Berhasil mempublikasikan {$subtest->total_questions} soal untuk subtest '{$subtest->name}'.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan ke database: ' . $e->getMessage())->withInput();
        }
    }
}
