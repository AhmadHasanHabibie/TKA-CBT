<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account
        User::updateOrCreate(
            ['email' => 'admin@tka.test'],
            [
                'name' => 'Administrator TKA',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Three Student (User) Accounts
        $users = [
            ['name' => 'Budi Santoso', 'email' => 'budi@tka.test'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@tka.test'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@tka.test'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Sample Subtest "Penalaran Umum" (30 mins, 5 complete questions)
        $subtest = Subtest::updateOrCreate(
            ['slug' => 'penalaran-umum'],
            [
                'name' => 'Penalaran Umum',
                'description' => 'Mata uji kemampuan bernalar logis, analitis, dan pemecahan masalah kuantitatif terstandar.',
                'duration_minutes' => 30,
                'total_questions' => 5,
                'is_active' => true,
            ]
        );

        // Wipe any existing questions for idempotent re-seeding
        $subtest->questions()->delete();

        $sampleQuestions = [
            [
                'number' => 1,
                'text' => 'Semua mamalia bernapas dengan paru-paru. Ikan paus adalah mamalia air. Kesimpulan yang paling tepat adalah ...',
                'options' => [
                    ['label' => 'A', 'text' => 'Semua hewan air bernapas dengan paru-paru.', 'is_correct' => false],
                    ['label' => 'B', 'text' => 'Ikan paus bernapas dengan paru-paru.', 'is_correct' => true],
                    ['label' => 'C', 'text' => 'Ikan paus bernapas dengan insang seperti ikan lainnya.', 'is_correct' => false],
                    ['label' => 'D', 'text' => 'Sebagian mamalia tidak bernapas dengan paru-paru.', 'is_correct' => false],
                    ['label' => 'E', 'text' => 'Hanya ikan paus mamalia yang bernapas dengan insang.', 'is_correct' => false],
                ],
                'explanation' => 'Premis mayor: Semua mamalia bernapas dengan paru-paru. Premis minor: Ikan paus adalah mamalia. Maka kesimpulan silogismenya adalah ikan paus bernapas dengan paru-paru (Pilihan B).',
            ],
            [
                'number' => 2,
                'type' => 'multiple',
                'text' => 'Manakah di antara pernyataan berikut yang BENAR mengenai ciri-ciri bangun datar segitiga siku-siku? (Pilihan Ganda Kompleks — jawaban benar lebih dari satu)',
                'options' => [
                    ['label' => 'A', 'text' => 'Memiliki tepat satu sudut bernilai 90 derajat.', 'is_correct' => true],
                    ['label' => 'B', 'text' => 'Jumlah ketiga sudut dalamnya selalu 180 derajat.', 'is_correct' => true],
                    ['label' => 'C', 'text' => 'Ketiga sisinya selalu memiliki panjang yang sama.', 'is_correct' => false],
                    ['label' => 'D', 'text' => 'Panjang sisi miring selalu lebih pendek dari sisi tegaknya.', 'is_correct' => false],
                    ['label' => 'E', 'text' => 'Seluruh sudut dalamnya adalah sudut lancip.', 'is_correct' => false],
                ],
                'explanation' => 'Pada segitiga siku-siku, tepat satu sudut bernilai 90° (Opsi A benar) dan jumlah sudut interior segitiga selalu 180° (Opsi B benar). Ketiga sisi tidak sama panjang dan sisi miring adalah sisi terpanjang (hipotenusa). Kunci: A, B.',
            ],
            [
                'number' => 3,
                'type' => 'statement',
                'text' => "Berdasarkan laporan riset iklim 2024, kenaikan suhu permukaan air laut memicu intensitas badai tropis di kawasan Asia Tenggara, sementara curah hujan ekstrem meningkat hingga 15% dalam satu dekade terakhir. Kendati demikian, sejumlah kawasan dataran tinggi justru mengalami penurunan debit air tanah akibat alih fungsi lahan.",
                'options' => [
                    ['label' => '1', 'text' => 'Kenaikan suhu laut berkontribusi terhadap frekuensi badai tropis di Asia Tenggara.', 'is_correct' => true],
                    ['label' => '2', 'text' => 'Curah hujan ekstrem di Asia Tenggara tidak mengalami perubahan selama satu dekade terakhir.', 'is_correct' => false],
                    ['label' => '3', 'text' => 'Penurunan debit air tanah di dataran tinggi dipengaruhi oleh alih fungsi lahan.', 'is_correct' => true],
                ],
                'explanation' => "Pernyataan 1: Sesuai (bacaan menyebutkan kenaikan suhu memicu intensitas badai tropis).\nPernyataan 2: Tidak Sesuai (bacaan menyebutkan curah hujan ekstrem meningkat hingga 15%).\nPernyataan 3: Sesuai (bacaan menyebutkan alih fungsi lahan memicu penurunan debit air tanah).\nKunci: S, TS, S.",
            ],
            [
                'number' => 4,
                'type' => 'single',
                'text' => 'Sebuah tangki air dapat diisi penuh oleh pipa A dalam 3 jam, dan oleh pipa B dalam 6 jam. Jika kedua pipa dibuka bersamaan, berapa jam tangki akan terisi penuh?',
                'options' => [
                    ['label' => 'A', 'text' => '1 jam', 'is_correct' => false],
                    ['label' => 'B', 'text' => '2 jam', 'is_correct' => true],
                    ['label' => 'C', 'text' => '3 jam', 'is_correct' => false],
                    ['label' => 'D', 'text' => '4 jam', 'is_correct' => false],
                    ['label' => 'E', 'text' => '4.5 jam', 'is_correct' => false],
                ],
                'explanation' => 'Debit kerja bersama: 1/T = 1/3 + 1/6 = 2/6 + 1/6 = 3/6 = 1/2. Maka waktu yang dibutuhkan T = 2 jam (Pilihan B).',
            ],
            [
                'number' => 5,
                'type' => 'single',
                'text' => 'KOSONG : HAMPA = ... : ...',
                'options' => [
                    ['label' => 'A', 'text' => 'Cair : Padat', 'is_correct' => false],
                    ['label' => 'B', 'text' => 'Penuh : Sesak', 'is_correct' => true],
                    ['label' => 'C', 'text' => 'Siang : Malam', 'is_correct' => false],
                    ['label' => 'D', 'text' => 'Panas : Dingin', 'is_correct' => false],
                    ['label' => 'E', 'text' => 'Tinggi : Rendah', 'is_correct' => false],
                ],
                'explanation' => 'Hubungan analogi sinonim: Kosong bersinonim dengan hampa. Padanan analogi yang tepat adalah Penuh bersinonim dengan sesak (Pilihan B).',
            ],
        ];

        foreach ($sampleQuestions as $q) {
            $question = Question::create([
                'subtest_id' => $subtest->id,
                'number' => $q['number'],
                'type' => $q['type'] ?? 'single',
                'text' => $q['text'],
                'explanation' => $q['explanation'],
            ]);

            foreach ($q['options'] as $opt) {
                Option::create([
                    'question_id' => $question->id,
                    'label' => $opt['label'],
                    'text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        }

        $subtest->updateQuestionCount();
    }
}
