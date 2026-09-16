<?php

namespace App\Services;

use Exception;
use Smalot\PdfParser\Parser;

class PdfParserService
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse uploaded PDF file into structured question array.
     *
     * @param string $filePath
     * @param int $subtestId
     * @return array
     * @throws Exception
     */
    public function parse(string $filePath, int $subtestId = 0): array
    {
        $pdf = $this->parser->parseFile($filePath);
        $rawText = $pdf->getText();

        $questions = $this->parseText($rawText);

        // Fail-safe image extraction and correlation
        try {
            $imagesByPage = $this->extractImagesFromDocument($pdf, $subtestId);
            $questions = $this->correlateImagesToQuestions($questions, $pdf->getPages(), $imagesByPage);
        } catch (\Throwable $e) {
            foreach ($questions as &$q) {
                if (!isset($q['image'])) {
                    $q['image'] = null;
                }
            }
        }

        return $questions;
    }

    /**
     * Parse raw extracted text into structured question array.
     *
     * @param string $text
     * @return array
     */
    public function parseText(string $text): array
    {
        // Normalize line breaks and spaces
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Replace Adobe Symbol / Word / LaTeX Private Use Area characters
        // to prevent tofu [] box rendering in web browsers
        $puaMap = [
            "\xef\xa3\xab" => "⎛",
            "\xef\xa3\xac" => "⎜",
            "\xef\xa3\xad" => "⎝",
            "\xef\xa3\xb6" => "⎞",
            "\xef\xa3\xb7" => "⎟",
            "\xef\xa3\xb8" => "⎠",
            "\xef\xa3\xa6" => "⎡",
            "\xef\xa3\xa7" => "⎢",
            "\xef\xa3\xa8" => "⎣",
            "\xef\xa3\xb1" => "⎤",
            "\xef\xa3\xb2" => "⎥",
            "\xef\xa3\xb3" => "⎦",
        ];
        $text = strtr($text, $puaMap);

        // Pattern to identify question beginnings: SOAL 1, SOAL 2, etc. (at beginning of line)
        $parts = preg_split('/(?:^|\n)\s*SOAL\s+(\d+)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (empty($parts) || count($parts) < 3) {
            // Fallback: try splitting by 'SOAL' if number is on next line or separated
            $parts = preg_split('/(?:^|\n)\s*SOAL\s*(\d*)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        }

        $questions = [];

        // $parts will look like: [intro_text, num1, content1, num2, content2, ...]
        for ($i = 1; $i < count($parts); $i += 2) {
            $number = !empty($parts[$i]) ? (int) $parts[$i] : (count($questions) + 1);
            $block = $parts[$i + 1] ?? '';

            if (trim($block) === '') {
                continue;
            }

            $parsedQuestion = $this->parseSingleBlock($block, $number);
            $questions[] = $parsedQuestion;
        }

        // If no questions found via SOAL header, attempt fallback line-by-line inspection
        if (empty($questions) && trim($text) !== '') {
            $questions[] = [
                'number' => 1,
                'type' => 'single',
                'text' => trim($text),
                'image' => null,
                'options' => [
                    ['label' => 'A', 'text' => '', 'is_correct' => false],
                    ['label' => 'B', 'text' => '', 'is_correct' => false],
                    ['label' => 'C', 'text' => '', 'is_correct' => false],
                    ['label' => 'D', 'text' => '', 'is_correct' => false],
                ],
                'keys' => ['A'],
                'key' => 'A',
                'explanation' => '',
                'has_error' => true,
                'error_message' => 'Format SOAL tidak terdeteksi otomatis. Silakan periksa format file PDF Anda.',
            ];
        }

        return $questions;
    }

    /**
     * Parse an individual question block.
     *
     * @param string $block
     * @param int $number
     * @return array
     */
    protected function parseSingleBlock(string $block, int $number): array
    {
        $hasError = false;
        $errorMessage = null;

        // 1. Extract KUNCI line
        $kunciRaw = '';
        if (preg_match('/KUNCI\s*:\s*([^\n\r]+)/i', $block, $kMatches)) {
            $kunciRaw = trim($kMatches[1]);
        }

        // 2. Extract PEMBAHASAN
        $explanation = '';
        if (preg_match('/PEMBAHASAN\s*:\s*([\s\S]*)$/i', $block, $matches)) {
            $explanation = trim($matches[1]);
        }

        // 3. Remove PEMBAHASAN and KUNCI from block to isolate Question Text & Content
        $contentBeforeKunci = preg_replace('/KUNCI\s*:[\s\S]*$/i', '', $block);

        // 4. CHECK FOR STATEMENT TYPE: marker PERNYATAAN:
        if (preg_match('/(?:^|\n)\s*PERNYATAAN\s*:\s*/i', $contentBeforeKunci, $pMatch, PREG_OFFSET_CAPTURE)) {
            $splitOffset = $pMatch[0][1];
            $questionText = trim(substr($contentBeforeKunci, 0, $splitOffset));
            $questionText = preg_replace('/^\s*(?:SOAL\s*\d*|\d+[\.\)])\s*/i', '', $questionText);
            $statementsSection = substr($contentBeforeKunci, $splitOffset + strlen($pMatch[0][0]));

            // Extract numbered statements (1., 2., 3., etc.)
            $statementTexts = [];
            if (preg_match_all('/(?:^|\n)\s*(\d+)[\.\)]\s*([\s\S]*?)(?=(?:^|\n)\s*\d+[\.\)]\s*|$)/', "\n" . $statementsSection, $stmtMatches, PREG_SET_ORDER)) {
                foreach ($stmtMatches as $sm) {
                    $sText = trim($sm[2]);
                    if ($sText !== '') {
                        $statementTexts[] = $sText;
                    }
                }
            }

            // Parse KUNCI tokens (S, TS, SESUAI, TIDAK SESUAI)
            $keyBooleans = [];
            $keyLabels = [];
            if (!empty($kunciRaw)) {
                $rawTokens = array_map('trim', explode(',', $kunciRaw));
                if (count($rawTokens) === 1 && preg_match('/\s+/', $rawTokens[0])) {
                    $rawTokens = preg_split('/\s+/', $rawTokens[0]);
                }

                foreach ($rawTokens as $tok) {
                    $clean = strtoupper(trim($tok));
                    if (in_array($clean, ['S', 'SESUAI'])) {
                        $keyBooleans[] = true;
                        $keyLabels[] = 'S';
                    } elseif (in_array($clean, ['TS', 'TIDAK SESUAI', 'TIDAKSESUAI'])) {
                        $keyBooleans[] = false;
                        $keyLabels[] = 'TS';
                    } elseif (!empty($clean)) {
                        $hasError = true;
                        $errorMessage = "Token kunci '{$clean}' tidak dikenali. Gunakan S (Sesuai) atau TS (Tidak Sesuai).";
                    }
                }
            }

            if (empty($keyLabels)) {
                $hasError = true;
                $errorMessage = 'Baris KUNCI: [S, TS, ...] untuk pernyataan tidak ditemukan atau tidak memuat format yang valid.';
            } elseif (count($statementTexts) < 2) {
                $hasError = true;
                $errorMessage = 'Soal tipe Sesuai/Tidak Sesuai minimal memiliki 2 butir pernyataan.';
            } elseif (count($keyBooleans) !== count($statementTexts)) {
                $hasError = true;
                $errorMessage = "Jumlah kunci jawaban (" . count($keyBooleans) . ") tidak sama dengan jumlah pernyataan (" . count($statementTexts) . ").";
            }

            if (empty($explanation)) {
                if (!$hasError) {
                    $hasError = true;
                    $errorMessage = 'Bagian PEMBAHASAN: tidak ditemukan atau kosong.';
                } else {
                    $errorMessage .= ' Bagian PEMBAHASAN: juga kosong.';
                }
            }

            // Assemble statement options
            $options = [];
            foreach ($statementTexts as $idx => $sText) {
                $options[] = [
                    'label' => (string) ($idx + 1),
                    'text' => $sText,
                    'is_correct' => $keyBooleans[$idx] ?? false,
                ];
            }

            return [
                'number' => $number,
                'type' => 'statement',
                'text' => $questionText,
                'image' => null,
                'options' => $options,
                'keys' => $keyLabels,
                'key' => implode(', ', $keyLabels),
                'explanation' => $explanation,
                'has_error' => $hasError,
                'error_message' => $errorMessage,
            ];
        }

        // 5. STANDARD MULTIPLE CHOICE (Single or PGK Multiple)
        $keys = [];
        if (!empty($kunciRaw)) {
            if (preg_match_all('/[A-Ea-e]/', $kunciRaw, $letterMatches)) {
                $keys = array_values(array_unique(array_map('strtoupper', $letterMatches[0])));
            }
        }

        $type = count($keys) > 1 ? 'multiple' : 'single';

        // Extract Options A, B, C, D, and optional E
        $optionPosA = preg_match('/(?:^|\n)\s*A\.\s*/i', $contentBeforeKunci, $aMatch, PREG_OFFSET_CAPTURE);
        $questionText = '';
        $options = [];

        if ($optionPosA && isset($aMatch[0][1])) {
            $splitOffset = $aMatch[0][1];
            $questionText = trim(substr($contentBeforeKunci, 0, $splitOffset));
            $optionsSection = substr($contentBeforeKunci, $splitOffset);

            // Regex to extract each option (A through E)
            // Supports A-D (4 options) or A-E (5 options)
            $labels = ['A', 'B', 'C', 'D', 'E'];
            $optionTexts = [];

            for ($idx = 0; $idx < count($labels); $idx++) {
                $cur = $labels[$idx];
                $next = $labels[$idx + 1] ?? null;

                if ($next) {
                    $pattern = '/(?:^|\n)\s*' . $cur . '\.\s*([\s\S]*?)(?=(?:^|\n)\s*' . $next . '\.\s*|$)/i';
                } else {
                    $pattern = '/(?:^|\n)\s*' . $cur . '\.\s*([\s\S]*)$/i';
                }

                if (preg_match($pattern, $optionsSection, $optMatch)) {
                    $optText = trim($optMatch[1]);
                    // Don't add empty E if it doesn't exist
                    if ($cur !== 'E' || $optText !== '') {
                        $optionTexts[$cur] = $optText;
                    }
                }
            }

            // Assemble options array
            $availableLabels = array_keys($optionTexts);
            foreach ($optionTexts as $lbl => $txt) {
                $options[] = [
                    'label' => $lbl,
                    'text' => $txt,
                    'is_correct' => in_array($lbl, $keys),
                ];
            }

            // Validate that keys exist in extracted options
            if (empty($keys)) {
                $hasError = true;
                $errorMessage = 'Baris KUNCI: [A-E] tidak ditemukan atau tidak memuat huruf kunci yang valid.';
            } else {
                $invalidKeys = array_diff($keys, $availableLabels);
                if (!empty($invalidKeys)) {
                    $hasError = true;
                    $errorMessage = "KUNCI '" . implode(', ', $invalidKeys) . "' tidak cocok dengan opsi yang terdeteksi (" . implode(', ', $availableLabels) . ").";
                }
            }

            if (empty($explanation)) {
                if (!$hasError) {
                    $hasError = true;
                    $errorMessage = 'Bagian PEMBAHASAN: tidak ditemukan atau kosong.';
                } else {
                    $errorMessage .= ' Bagian PEMBAHASAN: juga kosong.';
                }
            }
        } else {
            // Options A-E could not be isolated properly
            $questionText = trim($contentBeforeKunci);
            $hasError = true;
            $errorMessage = 'Opsi jawaban (A., B., C., D.) tidak berhasil dipisahkan secara otomatis.';

            // Provide blank fallback options
            $options = [
                ['label' => 'A', 'text' => '', 'is_correct' => false],
                ['label' => 'B', 'text' => '', 'is_correct' => false],
                ['label' => 'C', 'text' => '', 'is_correct' => false],
                ['label' => 'D', 'text' => '', 'is_correct' => false],
                ['label' => 'E', 'text' => '', 'is_correct' => false],
            ];
        }

        // Clean any leading/trailing question label from question text
        $questionText = preg_replace('/^\s*(?:SOAL\s*\d*|\d+[\.\)])\s*/i', '', $questionText);

        return [
            'number' => $number,
            'type' => $type,
            'text' => $questionText,
            'image' => null,
            'options' => $options,
            'keys' => $keys,
            'key' => !empty($keys) ? implode(', ', $keys) : 'A',
            'explanation' => $explanation,
            'has_error' => $hasError,
            'error_message' => $errorMessage,
        ];
    }

    /**
     * Extract images from PDF document and save to staging storage directory.
     *
     * @param \Smalot\PdfParser\Document $pdf
     * @param int $subtestId
     * @return array [pageNum => [relativeImagePath, ...]]
     */
    protected function extractImagesFromDocument($pdf, int $subtestId = 0): array
    {
        $stagingDir = storage_path('app/public/staging_images');
        if (!is_dir($stagingDir)) {
            @mkdir($stagingDir, 0755, true);
        }

        $imagesByPage = [];
        $seenHashes = [];

        try {
            $pages = $pdf->getPages();
            foreach ($pages as $pIdx => $page) {
                $pageNum = $pIdx + 1;
                try {
                    $xobjects = $page->getXObjects();
                } catch (\Throwable $e) {
                    continue;
                }

                $imgIdx = 0;
                foreach ($xobjects as $name => $xobj) {
                    try {
                        if (!($xobj instanceof \Smalot\PdfParser\XObject\Image)) {
                            $header = $xobj->getHeader();
                            $subtype = $header ? $header->get('Subtype') : null;
                            if (!$subtype || strtolower((string)$subtype->getContent()) !== 'image') {
                                continue;
                            }
                        }

                        $content = $xobj->getContent();
                        if (empty($content) || strlen($content) < 64) {
                            continue;
                        }

                        $hash = md5($content);
                        if (isset($seenHashes[$hash])) {
                            continue;
                        }
                        $seenHashes[$hash] = true;

                        $saved = false;
                        $filename = "img_sub_{$subtestId}_p{$pageNum}_{$imgIdx}_" . uniqid() . ".jpg";
                        $fullPath = $stagingDir . '/' . $filename;
                        $relPath = 'staging_images/' . $filename;

                        // Check JPEG magic bytes \xFF\xD8\xFF
                        if (substr($content, 0, 3) === "\xFF\xD8\xFF") {
                            file_put_contents($fullPath, $content);
                            $saved = true;
                        } else {
                            // Try GD imagecreatefromstring (PNG/GIF/BMP/WebP)
                            $res = @imagecreatefromstring($content);
                            if ($res) {
                                $filename = "img_sub_{$subtestId}_p{$pageNum}_{$imgIdx}_" . uniqid() . ".png";
                                $fullPath = $stagingDir . '/' . $filename;
                                $relPath = 'staging_images/' . $filename;
                                imagepng($res, $fullPath);
                                imagedestroy($res);
                                $saved = true;
                            }
                        }

                        if ($saved) {
                            $imagesByPage[$pageNum][] = $relPath;
                            $imgIdx++;
                        }
                    } catch (\Throwable $e) {
                        // Suppress per-image errors so parsing never crashes
                    }
                }
            }
        } catch (\Throwable $e) {
            // Suppress document-wide extraction errors
        }

        return $imagesByPage;
    }

    /**
     * Map extracted images to parsed questions.
     *
     * @param array $questions
     * @param array $pages
     * @param array $imagesByPage
     * @return array
     */
    protected function correlateImagesToQuestions(array $questions, array $pages, array $imagesByPage): array
    {
        if (empty($imagesByPage)) {
            return $questions;
        }

        // 1. Identify which questions start on which page
        $questionsOnPage = [];
        foreach ($pages as $pIdx => $page) {
            $pageNum = $pIdx + 1;
            try {
                $pText = $page->getText();
            } catch (\Throwable $e) {
                $pText = '';
            }

            foreach ($questions as $q) {
                $num = $q['number'];
                if (preg_match('/(?:^|\n)\s*SOAL\s+' . $num . '(?:\D|$)/i', $pText)) {
                    $questionsOnPage[$pageNum][] = $num;
                }
            }
        }

        // 2. Assign images on matching pages
        $assignedImages = []; // questionNumber => imagePath
        $unassignedImages = [];

        foreach ($imagesByPage as $pageNum => $imgs) {
            $qNums = $questionsOnPage[$pageNum] ?? [];
            if (!empty($qNums)) {
                foreach ($imgs as $idx => $img) {
                    if (isset($qNums[$idx])) {
                        $assignedImages[$qNums[$idx]] = $img;
                    } else {
                        $unassignedImages[] = $img;
                    }
                }
            } else {
                foreach ($imgs as $img) {
                    $unassignedImages[] = $img;
                }
            }
        }

        // 3. Fallback: distribute remaining unassigned images to questions that don't have images
        foreach ($questions as $q) {
            $num = $q['number'];
            if (!isset($assignedImages[$num]) && !empty($unassignedImages)) {
                $assignedImages[$num] = array_shift($unassignedImages);
            }
        }

        // 4. Attach to questions
        foreach ($questions as &$q) {
            $num = $q['number'];
            $q['image'] = $assignedImages[$num] ?? null;
        }

        return $questions;
    }
}
