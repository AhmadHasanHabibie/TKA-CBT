<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator Dokumentasi',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $users = [
            ['name' => 'Student One', 'email' => 'student1@example.com'],
            ['name' => 'Student Two', 'email' => 'student2@example.com'],
            ['name' => 'Student Three', 'email' => 'student3@example.com'],
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

$sampleAlbums = [
            [
                'title' => 'Dokumentasi MPLS & Pengenalan Lingkungan Sekolah',
                'school_name' => 'SMA Negeri 1 Jakarta',
                'slug' => 'dokumentasi-mpls-pengenalan-lingkungan-sekolah',
                'category' => 'Kegiatan Sekolah',
                'description' => 'Koleksi foto kegiatan Masa Pengenalan Lingkungan Sekolah (MPLS), apel pagi, dan perkenalan ekstrakurikuler siswa baru.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Album Kenangan Kelas XII & Wisuda Angkatan 2026',
                'school_name' => 'SMA Negeri 1 Jakarta',
                'slug' => 'album-kenangan-kelas-xii-wisuda-angkatan-2026',
                'category' => 'Wisuda & Angkatan',
                'description' => 'Dokumentasi sesi pemotretan bersama wali kelas, wisuda siswa angkatan, dan pelepasan kelulusan.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Pekan Olahraga & Pentas Seni Siswa (Classmeeting)',
                'school_name' => 'SMA Taruna Nusantara',
                'slug' => 'pekan-olahraga-pentas-seni-siswa-classmeeting',
                'category' => 'Ekstrakurikuler',
                'description' => 'Momen keseruan pertandingan olahraga antar kelas, pertunjukan musik siswa, dan pembagian piala juara umum.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            
            [
                'title' => 'Bulan Bahasa',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-bulan-bahasa-2026',
                'category' => 'Bulan Bahasa',
                'description' => 'Dokumentasi kegiatan Bulan Bahasa dan Sastra siswa SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'MPLS',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-mpls-2026',
                'category' => 'MPLS',
                'description' => 'Dokumentasi Masa Pengenalan Lingkungan Sekolah (MPLS) SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Class Meeting',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-class-meeting-2026',
                'category' => 'Class Meeting',
                'description' => 'Semarak pertandingan olahraga dan seni Class Meeting SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'LDKS',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-ldks-2026',
                'category' => 'LDKS',
                'description' => 'Latihan Dasar Kepemimpinan Siswa (LDKS) OSIS dan MPK SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => '17 Agustus',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-17-agustus-2026',
                'category' => '17 Agustus',
                'description' => 'Peringatan Hari Kemerdekaan Republik Indonesia 17 Agustus SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Hari Kartini',
                'school_name' => 'SMAN 25 Jakarta',
                'slug' => 'sman-25-jakarta-hari-kartini-2026',
                'category' => 'Hari Kartini',
                'description' => 'Peringatan Hari Kartini dan peragaan busana adat Nusantara SMAN 25 Jakarta tahun 2026.',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($sampleAlbums as $i => $albumData) {
            $album = QuestionBank::updateOrCreate(
                ['slug' => $albumData['slug']],
                $albumData
            );

            if ($album->items()->count() === 0) {
                $images = [
                    'images/documentation/student_event_1.jpg',
                    'images/documentation/student_event_2.jpg',
                    'images/documentation/student_event_3.jpg',
                ];
                $img = $images[$i % count($images)];
                \App\Models\QuestionBankItem::create([
                    'question_bank_id' => $album->id,
                    'image' => $img,
                    'title' => 'Foto Dokumentasi Kegiatan ' . ($i + 1),
                    'notes' => 'Momen kebersamaan siswa di ' . $albumData['school_name'],
                    'order' => 1,
                ]);
            }
        }
    }
}
