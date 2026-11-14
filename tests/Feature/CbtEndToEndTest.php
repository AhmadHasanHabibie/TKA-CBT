<?php

namespace Tests\Feature;

use App\Models\ExamSession;
use App\Models\Option;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CbtEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

public function test_login_flow_and_role_redirects(): void
    {
        
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/dokumentasi-siswa/login/admin');

        $response = $this->get('/user/dashboard');
        $response->assertRedirect('/login');

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/admin/dashboard');

        $admin = User::where('email', 'admin@example.com')->first();
        $response = $this->actingAs($admin)->get('/user/dashboard');
        $response->assertRedirect('/admin/dashboard');

        $user = User::where('email', 'student1@example.com')->first();
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertRedirect('/user/dashboard');

$response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
    }

public function test_admin_subtest_and_pdf_staging_publish(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

$response = $this->actingAs($admin)->post('/admin/subtests', [
            'name' => 'Matematika Dasar',
            'description' => 'Tes berhitung dan logika matematika',
            'duration_minutes' => 20,
        ]);
        $subtest = Subtest::where('name', 'Matematika Dasar')->first();
        $this->assertNotNull($subtest);
        $response->assertRedirect(route('admin.subtests.upload', $subtest));

$response = $this->actingAs($admin)->patch(route('admin.subtests.toggle', $subtest));
        $subtest->refresh();
        $this->assertFalse($subtest->is_active);

        $response = $this->actingAs($admin)->patch(route('admin.subtests.toggle', $subtest));
        $subtest->refresh();
        $this->assertTrue($subtest->is_active);

$response = $this->actingAs($admin)->get(route('admin.subtests.template.download'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

$samplePdfPath = storage_path('app/contoh_soal_tka.pdf');
        $uploadedFile = new UploadedFile($samplePdfPath, 'contoh_soal_tka.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($admin)->post(route('admin.subtests.upload.post', $subtest), [
            'pdf_file' => $uploadedFile,
        ]);
        $response->assertRedirect(route('admin.subtests.preview', $subtest));

$stagedQuestions = session("subtest_staging_{$subtest->id}");
        $this->assertIsArray($stagedQuestions);
        $this->assertCount(3, $stagedQuestions);
        $this->assertEquals('single', $stagedQuestions[0]['type']);
        $this->assertEquals('multiple', $stagedQuestions[1]['type']);
        $this->assertEquals('statement', $stagedQuestions[2]['type']);

$payload = [
            'questions' => [
                [
                    'type' => 'single',
                    'text' => 'Soal 1 Matematika',
                    'correct_key' => 'B',
                    'explanation' => 'Penjelasan soal 1',
                    'options' => [
                        ['label' => 'A', 'text' => 'Pilihan A'],
                        ['label' => 'B', 'text' => 'Pilihan B'],
                        ['label' => 'C', 'text' => 'Pilihan C'],
                        ['label' => 'D', 'text' => 'Pilihan D'],
                    ],
                ],
                [
                    'type' => 'multiple',
                    'text' => 'Soal 2 Matematika PGK',
                    'correct_keys' => ['A', 'C'],
                    'explanation' => 'Penjelasan soal 2 PGK',
                    'options' => [
                        ['label' => 'A', 'text' => 'Pilihan 2A'],
                        ['label' => 'B', 'text' => 'Pilihan 2B'],
                        ['label' => 'C', 'text' => 'Pilihan 2C'],
                        ['label' => 'D', 'text' => 'Pilihan 2D'],
                    ],
                ],
                [
                    'type' => 'statement',
                    'text' => 'Teks bacaan stimulus soal 3 Sesuai / Tidak Sesuai.',
                    'explanation' => 'Penjelasan soal 3 statement',
                    'options' => [
                        ['label' => '1', 'text' => 'Pernyataan 1', 'is_correct' => '1'],
                        ['label' => '2', 'text' => 'Pernyataan 2', 'is_correct' => '0'],
                        ['label' => '3', 'text' => 'Pernyataan 3', 'is_correct' => '1'],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.subtests.publish', $subtest), $payload);
        $response->assertRedirect(route('admin.subtests.index'));

$subtest->refresh();
        $this->assertEquals(3, $subtest->total_questions);
        $this->assertEquals(3, $subtest->questions()->count());

        $q2Staged = $subtest->questions()->where('number', 2)->first();
        $this->assertEquals('multiple', $q2Staged->type);
        $this->assertEquals(2, $q2Staged->options()->where('is_correct', true)->count());

        $q3Staged = $subtest->questions()->where('number', 3)->first();
        $this->assertEquals('statement', $q3Staged->type);
        $this->assertEquals(3, $q3Staged->options()->count());
        $this->assertEquals(2, $q3Staged->options()->where('is_correct', true)->count());
        $this->assertEquals(1, $q3Staged->options()->where('is_correct', false)->count());
    }

public function test_admin_user_management(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Student Four',
            'email' => 'student4@example.com',
            'password' => 'secret123',
            'role' => 'user',
        ]);
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'student4@example.com']);

        $student4 = User::where('email', 'student4@example.com')->first();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $student4), [
            'name' => 'Student Updated',
            'email' => 'student4@example.com',
            'role' => 'user',
        ]);
        $response->assertRedirect(route('admin.users.index'));
        $student4->refresh();
        $this->assertEquals('Student Updated', $student4->name);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $student4));
        $this->assertDatabaseMissing('users', ['id' => $student4->id]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_student_exam_lifecycle_and_grading(): void
    {
        $user = User::where('email', 'student1@example.com')->first();
        $subtest = Subtest::where('slug', 'penalaran-umum')->first();
        $this->assertNotNull($subtest);
        $this->assertEquals(5, $subtest->total_questions);

$response = $this->actingAs($user)->get(route('user.exam.start', $subtest));
        $response->assertRedirect(route('user.exam.board', $subtest));

$session = ExamSession::where('user_id', $user->id)
            ->where('subtest_id', $subtest->id)
            ->first();
        $this->assertNotNull($session);
        $this->assertEquals('ongoing', $session->status);
        $this->assertTrue($session->ends_at->isAfter(now()));

$response = $this->actingAs($user)->get(route('user.exam.board', $subtest));
        $response->assertStatus(200);
        $response->assertSee('Semua mamalia');
        $response->assertSee('mobileNavOpen');
        $response->assertSee('Navigasi');
        $response->assertDontSee('ring-offset-2');
        $response->assertDontSee('border-slate-900');

$q1 = $subtest->questions()->where('number', 1)->first();
        $q1CorrectOption = $q1->options()->where('is_correct', true)->first();

        $response = $this->actingAs($user)->postJson(route('user.exam.save-answer', $session), [
            'question_id' => $q1->id,
            'option_id' => $q1CorrectOption->id,
            'is_doubt' => true,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_doubt' => true]);

        $this->assertDatabaseHas('user_answers', [
            'exam_session_id' => $session->id,
            'question_id' => $q1->id,
            'is_doubt' => 1,
        ]);
        $this->assertDatabaseHas('user_answer_options', [
            'option_id' => $q1CorrectOption->id,
        ]);

$q2 = $subtest->questions()->where('number', 2)->first();
        $this->assertEquals('multiple', $q2->type);
        $q2CorrectOptionIds = $q2->options()->where('is_correct', true)->pluck('id')->all();

        $response = $this->actingAs($user)->postJson(route('user.exam.save-answer', $session), [
            'question_id' => $q2->id,
            'option_ids' => $q2CorrectOptionIds,
            'is_doubt' => false,
        ]);
        $response->assertStatus(200);

$q3 = $subtest->questions()->where('number', 3)->first();
        $this->assertEquals('statement', $q3->type);
        foreach ($q3->options as $stmtOpt) {
            $resp = $this->actingAs($user)->postJson(route('user.exam.save-answer', $session), [
                'question_id' => $q3->id,
                'option_id' => $stmtOpt->id,
                'value' => (bool) $stmtOpt->is_correct,
                'is_doubt' => false,
            ]);
            $resp->assertStatus(200);
            $this->assertDatabaseHas('user_answer_options', [
                'option_id' => $stmtOpt->id,
                'value' => (bool) $stmtOpt->is_correct ? 1 : 0,
            ]);
        }

$q4 = $subtest->questions()->where('number', 4)->first();
        $q4CorrectOpt = $q4->options()->where('is_correct', true)->first();
        $this->actingAs($user)->postJson(route('user.exam.save-answer', $session), [
            'question_id' => $q4->id,
            'option_ids' => [$q4CorrectOpt->id],
            'is_doubt' => false,
        ]);

$response = $this->actingAs($user)->postJson(route('user.exam.violation', $session));
        $response->assertStatus(200);
        $session->refresh();
        $this->assertEquals(1, $session->tab_violation_count);

$response = $this->actingAs($user)->post(route('user.exam.finish', $session));
        $response->assertRedirect(route('user.exam.result', $subtest));

$session->refresh();
        $this->assertEquals('finished', $session->status);
        $this->assertEquals(4, $session->correct_count);
        $this->assertEquals(80.00, (float) $session->score);

$response = $this->actingAs($user)->get(route('user.exam.result', $subtest));
        $response->assertStatus(200);
        $response->assertSee('80.00');
        $response->assertSee('Pembahasan');
        $response->assertSee('Pilihan Ganda Kompleks');
        $response->assertSee('Sesuai / Tidak Sesuai');
        $response->assertSee('Stimulus / Teks Bacaan');
        $response->assertSee('Jawaban Kamu');

$response = $this->actingAs($user)->get(route('user.exam.start', $subtest));
        $response->assertRedirect(route('user.exam.result', $subtest));

$response = $this->actingAs($user)->get(route('user.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Skor: 80.0');
    }

public function test_expired_session_auto_grading_guard(): void
    {
        $user = User::where('email', 'student2@example.com')->first();
        $subtest = Subtest::where('slug', 'penalaran-umum')->first();

$session = ExamSession::create([
            'user_id' => $user->id,
            'subtest_id' => $subtest->id,
            'started_at' => now()->subMinutes(35),
            'ends_at' => now()->subMinutes(5),
            'status' => 'ongoing',
        ]);

$q = $subtest->questions()->first();
        $opt = $q->options()->first();

        $response = $this->actingAs($user)->postJson(route('user.exam.save-answer', $session), [
            'question_id' => $q->id,
            'option_id' => $opt->id,
        ]);
        $response->assertStatus(410);

        $session->refresh();
        $this->assertEquals('finished', $session->status);
    }

public function test_pgk_all_or_nothing_grading(): void
    {
        $gradingService = app(\App\Services\ExamGradingService::class);
        $subtest = Subtest::where('slug', 'penalaran-umum')->first();
        $q2 = $subtest->questions()->where('number', 2)->first();
        $this->assertEquals('multiple', $q2->type);

        $optA = $q2->options()->where('label', 'A')->first(); 
        $optB = $q2->options()->where('label', 'B')->first(); 
        $optC = $q2->options()->where('label', 'C')->first(); 

$this->assertFalse($gradingService->isQuestionCorrect($q2, [$optA->id]));

$this->assertFalse($gradingService->isQuestionCorrect($q2, [$optB->id]));

$this->assertFalse($gradingService->isQuestionCorrect($q2, [$optA->id, $optB->id, $optC->id]));

$this->assertFalse($gradingService->isQuestionCorrect($q2, [$optC->id]));

$this->assertFalse($gradingService->isQuestionCorrect($q2, []));

$this->assertTrue($gradingService->isQuestionCorrect($q2, [$optA->id, $optB->id]));
        $this->assertTrue($gradingService->isQuestionCorrect($q2, [$optB->id, $optA->id]));
    }

public function test_statement_all_or_nothing_grading(): void
    {
        $gradingService = app(\App\Services\ExamGradingService::class);
        $subtest = Subtest::where('slug', 'penalaran-umum')->first();
        $q3 = $subtest->questions()->where('number', 3)->first();
        $this->assertEquals('statement', $q3->type);

        $opts = $q3->options->keyBy('label');
        $opt1 = $opts['1']; 
        $opt2 = $opts['2']; 
        $opt3 = $opts['3']; 

$allCorrect = [
            $opt1->id => true,
            $opt2->id => false,
            $opt3->id => true,
        ];
        $this->assertTrue($gradingService->isQuestionCorrect($q3, [], $allCorrect));

$oneWrong = [
            $opt1->id => true,
            $opt2->id => true,
            $opt3->id => true,
        ];
        $this->assertFalse($gradingService->isQuestionCorrect($q3, [], $oneWrong));

$partial = [
            $opt1->id => true,
            $opt2->id => false,
        ];
        $this->assertFalse($gradingService->isQuestionCorrect($q3, [], $partial));

$allOpposite = [
            $opt1->id => false,
            $opt2->id => true,
            $opt3->id => false,
        ];
        $this->assertFalse($gradingService->isQuestionCorrect($q3, [], $allOpposite));

$this->assertFalse($gradingService->isQuestionCorrect($q3, [], []));
    }

public function test_pdf_parser_service_detects_all_question_types(): void
    {
        $parser = new \App\Services\PdfParserService();

        $sampleText = "
SOAL 1
Teks pertanyaan pilihan ganda biasa nomor 1.
A. Pilihan A
B. Pilihan B
C. Pilihan C
D. Pilihan D
KUNCI: B
PEMBAHASAN: Pembahasan soal 1.

SOAL 2
Teks pertanyaan pilihan ganda kompleks nomor 2.
A. Ciri pertama
B. Ciri kedua
C. Ciri ketiga
D. Ciri keempat
KUNCI: A, D
PEMBAHASAN: Pembahasan soal 2.

SOAL 3
Sebuah teks bacaan stimulus untuk soal pernyataan bernomor 3.
PERNYATAAN:
1. Dampak negatif dialami ekosistem.
2. Dampak positif dialami oleh seluruh spesies.
3. Waktu penelitian adalah sepuluh tahun.
KUNCI: S, TS, S
PEMBAHASAN: Pembahasan soal 3.
";

        $parsed = $parser->parseText($sampleText);
        $this->assertCount(3, $parsed);

$this->assertEquals('single', $parsed[0]['type']);
        $this->assertEquals(['B'], $parsed[0]['keys']);

$this->assertEquals('multiple', $parsed[1]['type']);
        $this->assertEquals(['A', 'D'], $parsed[1]['keys']);

$this->assertEquals('statement', $parsed[2]['type']);
        $this->assertCount(3, $parsed[2]['options']);
        $this->assertEquals(['S', 'TS', 'S'], $parsed[2]['keys']);
        $this->assertEquals(true, $parsed[2]['options'][0]['is_correct']);
        $this->assertEquals(false, $parsed[2]['options'][1]['is_correct']);
        $this->assertEquals(true, $parsed[2]['options'][2]['is_correct']);
    }

public function test_question_image_upload_and_publishing(): void
    {
        $admin = User::where('role', 'admin')->first();
        $subtest = Subtest::where('slug', 'penalaran-umum')->first();

        \Illuminate\Support\Facades\Storage::fake('public');

$fakeImage = \Illuminate\Http\UploadedFile::fake()->image('diagram_soal.jpg', 400, 300);

        $payload = [
            'questions' => [
                0 => [
                    'type' => 'single',
                    'text' => 'Berdasarkan diagram rangkaian listrik di bawah, berapakah arusnya?',
                    'correct_key' => 'B',
                    'explanation' => 'Gunakan hukum Ohm V = I * R.',
                    'image_file' => $fakeImage,
                    'options' => [
                        0 => ['label' => 'A', 'text' => '2 Ampere'],
                        1 => ['label' => 'B', 'text' => '4 Ampere'],
                        2 => ['label' => 'C', 'text' => '6 Ampere'],
                        3 => ['label' => 'D', 'text' => '8 Ampere'],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.subtests.publish', $subtest), $payload);
        $response->assertRedirect(route('admin.subtests.index'));

        $question = $subtest->questions()->first();
        $this->assertNotNull($question);
        $this->assertTrue($question->hasImage());
        $this->assertNotNull($question->image);
        $this->assertStringStartsWith('questions/', $question->image);
        $this->assertNotNull($question->image_url);
        $this->assertStringContainsString('/storage/questions/', $question->image_url);

\Illuminate\Support\Facades\Storage::disk('public')->assertExists($question->image);

$user = User::where('role', 'user')->first();
        $session = \App\Models\ExamSession::where('user_id', $user->id)->where('subtest_id', $subtest->id)->first();
        if ($session) {
            $session->update(['status' => 'ongoing']);
            $boardResponse = $this->actingAs($user)->get(route('user.exam.board', $subtest));
            $boardResponse->assertStatus(200);
            $boardResponse->assertSee($question->image);
        }
    }

public function test_pdf_parser_service_extracts_images_from_pdf(): void
    {
        
        $im = imagecreatetruecolor(10, 10);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagefill($im, 0, 0, $red);
        ob_start();
        imagejpeg($im);
        $jpegBytes = ob_get_clean();
        imagedestroy($im);
        $streamLen = strlen($jpegBytes);

        $textStream = "BT\n/F1 12 Tf\n50 700 Td\n(SOAL 1) '\n(Apakah gambar ini berwarna merah?) '\n(A. Ya) '\n(B. Tidak) '\n(KUNCI: A) '\n(PEMBAHASAN: Warna merah.) '\nET\nq\n100 0 0 100 50 550 cm\n/Im1 Do\nQ\n";
        $textLen = strlen($textStream);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $offsets[1] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $offsets[2] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $offsets[3] = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> /XObject << /Im1 6 0 R >> >> >>\nendobj\n";
        $offsets[4] = strlen($pdf);
        $pdf .= "4 0 obj\n<< /Length {$textLen} >>\nstream\n{$textStream}endstream\nendobj\n";
        $offsets[5] = strlen($pdf);
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $offsets[6] = strlen($pdf);
        $pdf .= "6 0 obj\n<< /Type /XObject /Subtype /Image /Width 10 /Height 10 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length {$streamLen} >>\nstream\n{$jpegBytes}\nendstream\nendobj\n";
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 7\n0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        $tmpPdf = storage_path('app/test_img_parse.pdf');
        file_put_contents($tmpPdf, $pdf);

        $parserService = app(\App\Services\PdfParserService::class);
        $parsed = $parserService->parse($tmpPdf, 999);

        @unlink($tmpPdf);

        $this->assertCount(1, $parsed);
        $this->assertEquals(1, $parsed[0]['number']);
        $this->assertNotNull($parsed[0]['image']);
        $this->assertStringStartsWith('staging_images/', $parsed[0]['image']);

$this->assertFileExists(storage_path('app/public/' . $parsed[0]['image']));

@unlink(storage_path('app/public/' . $parsed[0]['image']));
    }
}

