<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Services\LoginTrackerService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Get list of standard schools: SMAN 1 Jakarta - SMAN 30 Jakarta merged with database schools.
     */
    public static function getSchoolOptions(): array
    {
        $defaultSchools = [];
        for ($i = 1; $i <= 30; $i++) {
            $defaultSchools[] = "SMAN {$i} Jakarta";
        }

        try {
            $dbSchools = QuestionBank::whereNotNull('school_name')
                ->where('school_name', '!=', '')
                ->distinct()
                ->pluck('school_name')
                ->toArray();
        } catch (\Throwable $e) {
            $dbSchools = [];
        }

        $merged = array_unique(array_merge($defaultSchools, $dbSchools));
        sort($merged, SORT_NATURAL | SORT_FLAG_CASE);

        return $merged;
    }

    /**
     * Get tailored album list and photo count for a given school.
     * Special specification for SMAN 25 Jakarta: Bulan Bahasa, MPLS, Class Meeting, LDKS, 17 Agustus, Hari Kartini (All 2026, 237 photos).
     * SMAN 1 - SMAN 30 Jakarta: Each has completely unique albums, subtitles, and photo counts.
     */
    public static function getAlbumsForSchool(string $schoolName): array
    {
        $schoolName = trim($schoolName);

        // 1. SMAN 25 Jakarta: Special protected configuration (Strict constraint)
        if (preg_match('/sman\s*25\b/i', $schoolName)) {
            return [
                'is_sman25' => true,
                'photos_count' => 237,
                'albums' => [
                    [
                        'title' => 'Bulan Bahasa',
                        'year' => '2026',
                        'subtitle' => 'Peringatan Bulan Bahasa & Sastra Siswa',
                        'photos' => '38 Foto',
                        'image' => asset('images/documentation/student_event_1.jpg'),
                    ],
                    [
                        'title' => 'MPLS',
                        'year' => '2026',
                        'subtitle' => 'Masa Pengenalan Lingkungan Sekolah',
                        'photos' => '45 Foto',
                        'image' => asset('images/documentation/student_event_2.jpg'),
                    ],
                    [
                        'title' => 'Class Meeting',
                        'year' => '2026',
                        'subtitle' => 'Pekan Olahraga & Seni Antar Kelas',
                        'photos' => '52 Foto',
                        'image' => asset('images/documentation/student_event_3.jpg'),
                    ],
                    [
                        'title' => 'LDKS',
                        'year' => '2026',
                        'subtitle' => 'Latihan Dasar Kepemimpinan Siswa',
                        'photos' => '34 Foto',
                        'image' => asset('images/documentation/student_event_2.jpg'),
                    ],
                    [
                        'title' => '17 Agustus',
                        'year' => '2026',
                        'subtitle' => 'Semarak Kemerdekaan RI 17 Agustus',
                        'photos' => '41 Foto',
                        'image' => asset('images/documentation/student_event_1.jpg'),
                    ],
                    [
                        'title' => 'Hari Kartini',
                        'year' => '2026',
                        'subtitle' => 'Peringatan Hari Kartini & Budaya Nusantara',
                        'photos' => '27 Foto',
                        'image' => asset('images/documentation/student_event_3.jpg'),
                    ],
                ],
            ];
        }

        $img1 = asset('images/documentation/student_event_1.jpg');
        $img2 = asset('images/documentation/student_event_2.jpg');
        $img3 = asset('images/documentation/student_event_3.jpg');

        // 2. Individual unique profiles for SMAN 1 through SMAN 30 Jakarta (Excluding 25)
        $schoolProfiles = [
            1 => [
                'photos_count' => 194,
                'albums' => [
                    ['title' => 'Festival Seni Budaya & Akustik Pelajar', 'year' => '2024', 'subtitle' => 'Pentas Musik Akustik & Teater Modern Boedoet', 'photos' => '68 Foto', 'image' => $img1],
                    ['title' => 'Olimpiade Sains & Inovasi Teknologi', 'year' => '2025', 'subtitle' => 'Kompetisi Matematika, Fisika & Robotik', 'photos' => '54 Foto', 'image' => $img2],
                    ['title' => 'Wisuda Angkatan & Pelepasan Siswa', 'year' => '2026', 'subtitle' => 'Sidang Terbuka & Syukuran Kelulusan Siswa', 'photos' => '72 Foto', 'image' => $img3],
                ],
            ],
            2 => [
                'photos_count' => 156,
                'albums' => [
                    ['title' => 'Pekan Olahraga & Liga Futsal Sekolah', 'year' => '2024', 'subtitle' => 'Turnamen Futsal & Basket Antar Kelas', 'photos' => '48 Foto', 'image' => $img2],
                    ['title' => 'Karya Ilmiah Remaja & Riset Lingkungan', 'year' => '2025', 'subtitle' => 'Eksibisi Biologi & Daur Ulang Mandiri', 'photos' => '42 Foto', 'image' => $img3],
                    ['title' => 'Malam Penganugerahan Prestasi Siswa', 'year' => '2026', 'subtitle' => 'Penghargaan Siswa Berprestasi & Seni Tari', 'photos' => '66 Foto', 'image' => $img1],
                ],
            ],
            3 => [
                'photos_count' => 248,
                'albums' => [
                    ['title' => 'Teladan Cup & Pesta Tari Tradisional', 'year' => '2024', 'subtitle' => 'Kompetisi Olahraga Nasional & Saman Kolosal', 'photos' => '82 Foto', 'image' => $img3],
                    ['title' => 'Simposium Pemimpin Muda & Debat', 'year' => '2025', 'subtitle' => 'Forum Kepemimpinan & Debat Bahasa Inggris', 'photos' => '76 Foto', 'image' => $img1],
                    ['title' => 'Gala Prom & Pelepasan Angkatan Emas', 'year' => '2026', 'subtitle' => 'Malam Kebersamaan & Wisuda Akbar Siswa', 'photos' => '90 Foto', 'image' => $img2],
                ],
            ],
            4 => [
                'photos_count' => 138,
                'albums' => [
                    ['title' => 'Karnaval Budaya Nusantara & Kuliner', 'year' => '2024', 'subtitle' => 'Parade Baju Adat & Festival Jajanan Tradisional', 'photos' => '44 Foto', 'image' => $img1],
                    ['title' => 'Latihan Dasar Kepemimpinan (LDKS)', 'year' => '2025', 'subtitle' => 'Pembinaan Mental & Karakter di Alam Terbuka', 'photos' => '46 Foto', 'image' => $img2],
                    ['title' => 'Gelar Karya Proyek P5 & Seni Rupa', 'year' => '2026', 'subtitle' => 'Pameran Instalasi Seni & Kewirausahaan Mandiri', 'photos' => '48 Foto', 'image' => $img3],
                ],
            ],
            5 => [
                'photos_count' => 172,
                'albums' => [
                    ['title' => 'Gebyar Bulan Bahasa & Cipta Puisi', 'year' => '2024', 'subtitle' => 'Lomba Teater Monolog & Musikalisasi Sastra', 'photos' => '52 Foto', 'image' => $img2],
                    ['title' => 'Kemah Bakti Pramuka Penegak', 'year' => '2025', 'subtitle' => 'Jambore Sekolah & Penjelajahan Hutan Lindung', 'photos' => '58 Foto', 'image' => $img3],
                    ['title' => 'Pentas Seni Suara & Band Festival', 'year' => '2026', 'subtitle' => 'Konser Musik Kolaborasi Angkatan Kelulusan', 'photos' => '62 Foto', 'image' => $img1],
                ],
            ],
            6 => [
                'photos_count' => 215,
                'albums' => [
                    ['title' => 'Mahakam Cup & Gelora Suporter', 'year' => '2024', 'subtitle' => 'Pertandingan Basket & Tari Modern DBL', 'photos' => '78 Foto', 'image' => $img3],
                    ['title' => 'Workshop Jurnalistik & Sinematografi', 'year' => '2025', 'subtitle' => 'Festival Film Pendek Karya Kreatif Siswa', 'photos' => '64 Foto', 'image' => $img1],
                    ['title' => 'Perayaan Pelepasan Siswa & Selebrasi', 'year' => '2026', 'subtitle' => 'Upacara Pelepasan & Konvoi Seragam Putih Abu', 'photos' => '73 Foto', 'image' => $img2],
                ],
            ],
            7 => [
                'photos_count' => 145,
                'albums' => [
                    ['title' => 'Bakti Sosial & Donor Darah PMR', 'year' => '2024', 'subtitle' => 'Aksi Kemanusiaan Palang Merah Remaja', 'photos' => '45 Foto', 'image' => $img1],
                    ['title' => 'Pekan Kreativitas Desain & Fotografi', 'year' => '2025', 'subtitle' => 'Galeri Seni Visual & Instalasi Digital Siswa', 'photos' => '47 Foto', 'image' => $img2],
                    ['title' => 'Pelepasan Angkatan & Pentas Musik', 'year' => '2026', 'subtitle' => 'Malam Perpisahan dan Penyerahan Ijazah', 'photos' => '53 Foto', 'image' => $img3],
                ],
            ],
            8 => [
                'photos_count' => 280,
                'albums' => [
                    ['title' => 'Olimpiade Riset & Cendekia Nusantara', 'year' => '2024', 'subtitle' => 'Pameran Karya Sains Internasional & Robotika Bukit Duri', 'photos' => '95 Foto', 'image' => $img2],
                    ['title' => 'Festival Paduan Suara & Orkestra', 'year' => '2025', 'subtitle' => 'Konser Harmoni Simfoni Pelajar Berbakat', 'photos' => '88 Foto', 'image' => $img3],
                    ['title' => 'Sidang Senat Wisuda & Pelepasan Siswa', 'year' => '2026', 'subtitle' => 'Prosesi Wisuda Siswa Lulusan PTN Favorit', 'photos' => '97 Foto', 'image' => $img1],
                ],
            ],
            9 => [
                'photos_count' => 132,
                'albums' => [
                    ['title' => 'Lomba Baris Berbaris & Paskibraka', 'year' => '2024', 'subtitle' => 'Kompetisi Formasi Variasi Pengibar Bendera', 'photos' => '42 Foto', 'image' => $img3],
                    ['title' => 'Festival Bahasa Asing & Budaya Dunia', 'year' => '2025', 'subtitle' => 'Eksibisi Bahasa Jepang, Jerman & Inggris', 'photos' => '40 Foto', 'image' => $img1],
                    ['title' => 'Peringatan Hari Guru & Seni Panggung', 'year' => '2026', 'subtitle' => 'Persembahan Karya & Dedikasi untuk Para Guru', 'photos' => '50 Foto', 'image' => $img2],
                ],
            ],
            10 => [
                'photos_count' => 164,
                'albums' => [
                    ['title' => 'Classmeeting Akbar & Tarik Tambang', 'year' => '2024', 'subtitle' => 'Keseruan Olahraga Rekreasi Antar Angkatan', 'photos' => '50 Foto', 'image' => $img1],
                    ['title' => 'Edukasi Peduli Lingkungan & Adiwiyata', 'year' => '2025', 'subtitle' => 'Gerakan Green School & Penanaman Pohon Rindang', 'photos' => '48 Foto', 'image' => $img2],
                    ['title' => 'Pelepasan Alumni & Malam Syukuran', 'year' => '2026', 'subtitle' => 'Syukuran Kelulusan & Kilas Balik Angkatan', 'photos' => '66 Foto', 'image' => $img3],
                ],
            ],
            11 => [
                'photos_count' => 148,
                'albums' => [
                    ['title' => 'Pekan Seni Tradisional & Angklung', 'year' => '2024', 'subtitle' => 'Harmoni Musik Tradisi & Tari Saman Kolosal', 'photos' => '46 Foto', 'image' => $img2],
                    ['title' => 'Latihan Bela Negara & Kedisiplinan', 'year' => '2025', 'subtitle' => 'Pelatihan Kepemimpinan & Ketahanan Fisik Siswa', 'photos' => '48 Foto', 'image' => $img3],
                    ['title' => 'Perayaan Wisuda & Kilas Balik Masa SMA', 'year' => '2026', 'subtitle' => 'Upacara Kelulusan & Penayangan Video Angkatan', 'photos' => '54 Foto', 'image' => $img1],
                ],
            ],
            12 => [
                'photos_count' => 175,
                'albums' => [
                    ['title' => 'Turnamen Voli & Bulutangkis Sekolah', 'year' => '2024', 'subtitle' => 'Piala Bergilir Antar Kelas & Dewan Guru', 'photos' => '55 Foto', 'image' => $img3],
                    ['title' => 'Kunjungan Industri & Kampus Prestasi', 'year' => '2025', 'subtitle' => 'Studi Ekskursi ke Perguruan Tinggi Negeri Unggulan', 'photos' => '52 Foto', 'image' => $img1],
                    ['title' => 'Pentas Seni Panggung Spektakuler', 'year' => '2026', 'subtitle' => 'Konser Musik Indie Pelajar & Pesta Cahaya', 'photos' => '68 Foto', 'image' => $img2],
                ],
            ],
            13 => [
                'photos_count' => 139,
                'albums' => [
                    ['title' => 'Peringatan Hari Pahlawan & Teatrikal', 'year' => '2024', 'subtitle' => 'Drama Kolosal Sejarah Perjuangan Bangsa', 'photos' => '43 Foto', 'image' => $img1],
                    ['title' => 'Bursa Wirausaha Siswa (Market Day)', 'year' => '2025', 'subtitle' => 'Gelar Produk Kreatif, Kerajinan & Kuliner Mandiri', 'photos' => '45 Foto', 'image' => $img2],
                    ['title' => 'Wisuda Angkatan & Janji Alumni', 'year' => '2026', 'subtitle' => 'Pengukuhan Lulusan & Pelepasan Balon Harapan', 'photos' => '51 Foto', 'image' => $img3],
                ],
            ],
            14 => [
                'photos_count' => 162,
                'albums' => [
                    ['title' => 'Festival Komik & Ilustrasi Digital', 'year' => '2024', 'subtitle' => 'Karya Seni Desain Karakter & Animasi Siswa', 'photos' => '49 Foto', 'image' => $img2],
                    ['title' => 'Kemah Pramuka Blok & Api Unggun', 'year' => '2025', 'subtitle' => 'Malam Keakraban di Bumi Perkemahan Pramuka', 'photos' => '53 Foto', 'image' => $img3],
                    ['title' => 'Wisuda Terbuka & Foto Bersama Angkatan', 'year' => '2026', 'subtitle' => 'Momen Abadi Bersama Wali Kelas & Almamater', 'photos' => '60 Foto', 'image' => $img1],
                ],
            ],
            15 => [
                'photos_count' => 153,
                'albums' => [
                    ['title' => 'Pekan Olahraga Santai & Senam Massal', 'year' => '2024', 'subtitle' => 'Senam Kesegaran Jasmani & Jalan Sehat Gembira', 'photos' => '47 Foto', 'image' => $img3],
                    ['title' => 'Pameran Karya Fotografi Estetika', 'year' => '2025', 'subtitle' => 'Eksibisi Foto Human Interest & Arsitektur Kota', 'photos' => '49 Foto', 'image' => $img1],
                    ['title' => 'Malam Pelepasan Siswa & Tari Kolaborasi', 'year' => '2026', 'subtitle' => 'Penyerahan Medali & Pementasan Seni Perpisahan', 'photos' => '57 Foto', 'image' => $img2],
                ],
            ],
            16 => [
                'photos_count' => 188,
                'albums' => [
                    ['title' => 'Festival Teater Modern & Monolog', 'year' => '2024', 'subtitle' => 'Pementasan Drama Naskah Sastra Indonesia', 'photos' => '58 Foto', 'image' => $img1],
                    ['title' => 'Pekan Inovasi Sains & Coding Pemula', 'year' => '2025', 'subtitle' => 'Demo Aplikasi Web & Robotik Sensor Siswa', 'photos' => '60 Foto', 'image' => $img2],
                    ['title' => 'Pesta Wisuda & Ramah Tamah Angkatan', 'year' => '2026', 'subtitle' => 'Perayaan Kebersamaan & Penyerahan Piagam Bintang', 'photos' => '70 Foto', 'image' => $img3],
                ],
            ],
            17 => [
                'photos_count' => 126,
                'albums' => [
                    ['title' => 'Semarak Peringatan Hari Kemerdekaan', 'year' => '2024', 'subtitle' => 'Lomba Tradisional 17 Agustus & Karnaval Busana', 'photos' => '40 Foto', 'image' => $img2],
                    ['title' => 'Simulasi Sidang PBB (Model UN) Siswa', 'year' => '2025', 'subtitle' => 'Debat Diplomasi Internasional Berbahasa Inggris', 'photos' => '38 Foto', 'image' => $img3],
                    ['title' => 'Pelepasan Siswa & Pengalungan Gordon', 'year' => '2026', 'subtitle' => 'Prosesi Kelulusan Resmi Siswa Tingkat Akhir', 'photos' => '48 Foto', 'image' => $img1],
                ],
            ],
            18 => [
                'photos_count' => 141,
                'albums' => [
                    ['title' => 'Lomba Musikalisasi Puisi Tingkat Kota', 'year' => '2024', 'subtitle' => 'Penampilan Akustik & Puisi Karya Orisinil Siswa', 'photos' => '44 Foto', 'image' => $img3],
                    ['title' => 'Eksplorasi Ekosistem Laut & Pesisir', 'year' => '2025', 'subtitle' => 'Studi Lapangan Konservasi Bakau Kepulauan Seribu', 'photos' => '46 Foto', 'image' => $img1],
                    ['title' => 'Wisuda Khidmat & Gelar Seni Perpisahan', 'year' => '2026', 'subtitle' => 'Upacara Penamatan & Doa Bersama Lulusan', 'photos' => '51 Foto', 'image' => $img2],
                ],
            ],
            19 => [
                'photos_count' => 167,
                'albums' => [
                    ['title' => 'Pentas Seni Urban & Musik Akustik', 'year' => '2024', 'subtitle' => 'Festival Band Sekolah & Bazar Kreatif Pemuda', 'photos' => '54 Foto', 'image' => $img1],
                    ['title' => 'Pelatihan Tanggap Bencana & Relawan', 'year' => '2025', 'subtitle' => 'Simulasi Penyelamatan & Pertolongan Pertama PMR', 'photos' => '51 Foto', 'image' => $img2],
                    ['title' => 'Malam Keakraban Alumni & Pelepasan', 'year' => '2026', 'subtitle' => 'Malam Kilas Balik Nostalgia Tiga Tahun SMA', 'photos' => '62 Foto', 'image' => $img3],
                ],
            ],
            20 => [
                'photos_count' => 158,
                'albums' => [
                    ['title' => 'Festival Budaya Betawi & Ondel-ondel', 'year' => '2024', 'subtitle' => 'Pentas Silat Beksi, Palang Pintu & Tari Sirih Kuning', 'photos' => '50 Foto', 'image' => $img2],
                    ['title' => 'Pameran Lukisan & Kaligrafi Islami', 'year' => '2025', 'subtitle' => 'Eksibisi Karya Seni Rupa & Kanvas Cat Minyak Siswa', 'photos' => '48 Foto', 'image' => $img3],
                    ['title' => 'Upacara Kelulusan & Pelepasan Siswa', 'year' => '2026', 'subtitle' => 'Penganugerahan Bintang Pelajar & Ijazah Angkatan', 'photos' => '60 Foto', 'image' => $img1],
                ],
            ],
            21 => [
                'photos_count' => 192,
                'albums' => [
                    ['title' => 'Kompetisi Paduan Suara & Vokal Solo', 'year' => '2024', 'subtitle' => 'Festival Lagu Populer & Daerah Nusantara', 'photos' => '61 Foto', 'image' => $img3],
                    ['title' => 'Riset Antariksa & Observasi Astronomi', 'year' => '2025', 'subtitle' => 'Peneropongan Bintang Malam & Mini Planetarium', 'photos' => '59 Foto', 'image' => $img1],
                    ['title' => 'Pelepasan Angkatan & Flashmob Massal', 'year' => '2026', 'subtitle' => 'Selebrasi Flashmob Kelulusan Siswa Tingkat Akhir', 'photos' => '72 Foto', 'image' => $img2],
                ],
            ],
            22 => [
                'photos_count' => 135,
                'albums' => [
                    ['title' => 'Class Meeting & Turnamen E-Sport', 'year' => '2024', 'subtitle' => 'Pertandingan Mobile Legends & Catur Antar Kelas', 'photos' => '42 Foto', 'image' => $img1],
                    ['title' => 'Kunjungan Museum & Sejarah Jakarta', 'year' => '2025', 'subtitle' => 'Jelajah Arkeologi Kota Tua & Museum Nasional', 'photos' => '43 Foto', 'image' => $img2],
                    ['title' => 'Syukuran Kelulusan & Wisuda Akbar', 'year' => '2026', 'subtitle' => 'Prosesi Pengukuhan & Tabur Bunga Wisudawan', 'photos' => '50 Foto', 'image' => $img3],
                ],
            ],
            23 => [
                'photos_count' => 147,
                'albums' => [
                    ['title' => 'Gelar Karya Batik Tulis & Kerajinan', 'year' => '2024', 'subtitle' => 'Praktik Seni Membatik Tradisional Siswa Seni Budaya', 'photos' => '46 Foto', 'image' => $img2],
                    ['title' => 'Pekan Kesehatan Jiwa & Remaja Sehat', 'year' => '2025', 'subtitle' => 'Seminar Psikologi Siswa & Konseling Sebaya', 'photos' => '47 Foto', 'image' => $img3],
                    ['title' => 'Malam Purnawiyata & Penganugerahan', 'year' => '2026', 'subtitle' => 'Wisuda Kehormatan & Pesta Pelepasan Angkatan', 'photos' => '54 Foto', 'image' => $img1],
                ],
            ],
            24 => [
                'photos_count' => 169,
                'albums' => [
                    ['title' => 'Festival Komedi Tunggal & Teater Musikal', 'year' => '2024', 'subtitle' => 'Panggung Seni Lawak Edukatif & Musikalisasi Naskah', 'photos' => '53 Foto', 'image' => $img3],
                    ['title' => 'Eksperimen Energi Terbarukan & Surya', 'year' => '2025', 'subtitle' => 'Uji Coba Miniatur Pembangkit Listrik Ramah Lingkungan', 'photos' => '51 Foto', 'image' => $img1],
                    ['title' => 'Pelepasan Siswa & Kilas Balik Putih Abu', 'year' => '2026', 'subtitle' => 'Penayangan Dokumenter Nostalgia Tiga Tahun SMA', 'photos' => '65 Foto', 'image' => $img2],
                ],
            ],
            26 => [
                'photos_count' => 204,
                'albums' => [
                    ['title' => 'Tari Saman Kolosal & Kreasi Tradisi', 'year' => '2024', 'subtitle' => 'Pementasan 100 Penari Saman Serempak', 'photos' => '66 Foto', 'image' => $img1],
                    ['title' => 'Pameran Desain Grafis & UI/UX Kreatif', 'year' => '2025', 'subtitle' => 'Pameran Desain Produk & Portofolio Digital', 'photos' => '62 Foto', 'image' => $img2],
                    ['title' => 'Wisuda Angkatan & Konser Pelepasan', 'year' => '2026', 'subtitle' => 'Gala Wisuda & Doa Bersama Menuju Kampus Impian', 'photos' => '76 Foto', 'image' => $img3],
                ],
            ],
            27 => [
                'photos_count' => 143,
                'albums' => [
                    ['title' => 'Pekan Olahraga Tradisional & Gobak Sodor', 'year' => '2024', 'subtitle' => 'Pelestarian Permainan Tradisional Pelajar', 'photos' => '45 Foto', 'image' => $img2],
                    ['title' => 'Gerakan Literasi & Bedah Buku Siswa', 'year' => '2025', 'subtitle' => 'Peluncuran Antologi Puisi & Cerpen Penulis Muda', 'photos' => '44 Foto', 'image' => $img3],
                    ['title' => 'Pelepasan Siswa & Pengalungan Medali', 'year' => '2026', 'subtitle' => 'Upacara Pelepasan Khidmat Bersama Orang Tua', 'photos' => '54 Foto', 'image' => $img1],
                ],
            ],
            28 => [
                'photos_count' => 260,
                'albums' => [
                    ['title' => 'Gebyar Festival Budaya & Orkestra Pelajar', 'year' => '2024', 'subtitle' => 'Pagelaran Musik Klasik & Kolaborasi Etnik Nusantara', 'photos' => '85 Foto', 'image' => $img3],
                    ['title' => 'Kompetisi Robotik & Artificial Intelligence', 'year' => '2025', 'subtitle' => 'Ajang Cipta Inovasi Sensor & Otomasi Cerdas', 'photos' => '82 Foto', 'image' => $img1],
                    ['title' => 'Purnawiyata Angkatan & Wisuda Kehormatan', 'year' => '2026', 'subtitle' => 'Sidang Terbuka Kelulusan & Selebrasi Menembus PTN', 'photos' => '93 Foto', 'image' => $img2],
                ],
            ],
            29 => [
                'photos_count' => 152,
                'albums' => [
                    ['title' => 'Lomba Catur Cepat & Tenis Meja Pelajar', 'year' => '2024', 'subtitle' => 'Pekan Olahraga Konsentrasi & Strategi Siswa', 'photos' => '48 Foto', 'image' => $img1],
                    ['title' => 'Pelatihan Public Speaking & Debat Hukum', 'year' => '2025', 'subtitle' => 'Asah Kemampuan Orasi & Argumentasi Kritis Siswa', 'photos' => '47 Foto', 'image' => $img2],
                    ['title' => 'Pelepasan Siswa & Pesta Kembang Api', 'year' => '2026', 'subtitle' => 'Malam Penuh Kenangan & Penutupan Masa Indah SMA', 'photos' => '57 Foto', 'image' => $img3],
                ],
            ],
            30 => [
                'photos_count' => 181,
                'albums' => [
                    ['title' => 'Semarak Pensi & Festival Band Indie Pelajar', 'year' => '2024', 'subtitle' => 'Panggung Seni Musik Siswa & Bazar Makanan Kreatif', 'photos' => '58 Foto', 'image' => $img2],
                    ['title' => 'Kemah Bakti Alam Bebas & Navigasi Darat', 'year' => '2025', 'subtitle' => 'Latihan Ketahanan Fisik & Survival Gunung Salak', 'photos' => '56 Foto', 'image' => $img3],
                    ['title' => 'Wisuda Akbar & Parade Kelulusan Siswa', 'year' => '2026', 'subtitle' => 'Pelepasan Balon Harapan & Ikrar Alumni Angkatan', 'photos' => '67 Foto', 'image' => $img1],
                ],
            ],
        ];

        // Match numeric SMAN index (1 to 30)
        if (preg_match('/sman\s*(\d+)\b/i', $schoolName, $matches)) {
            $num = (int) $matches[1];
            if (isset($schoolProfiles[$num])) {
                return [
                    'is_sman25' => false,
                    'photos_count' => $schoolProfiles[$num]['photos_count'],
                    'albums' => $schoolProfiles[$num]['albums'],
                ];
            }
        }

        // 3. Fallback for custom schools (from Database or external input)
        // Deterministic generation using crc32 so each custom school receives a unique profile
        $hash = !empty($schoolName) ? abs(crc32($schoolName)) : 42;
        $genCount = 110 + ($hash % 120); // 110 - 229 photos
        $p1 = (int) round($genCount * 0.36);
        $p2 = (int) round($genCount * 0.29);
        $p3 = $genCount - $p1 - $p2;

        $displayName = !empty($schoolName) ? $schoolName : 'Sekolah Menengah Atas';

        return [
            'is_sman25' => false,
            'photos_count' => $genCount,
            'albums' => [
                [
                    'title' => 'Pekan Seni & Kreativitas Pelajar',
                    'year' => '2024',
                    'subtitle' => 'Dokumentasi Pentas Musik & Pameran Budaya ' . $displayName,
                    'photos' => "{$p1} Foto",
                    'image' => $img1,
                ],
                [
                    'title' => 'Olimpiade Sains & Inovasi Teknologi',
                    'year' => '2025',
                    'subtitle' => 'Eksibisi Proyek Ilmiah & Prestasi Siswa ' . $displayName,
                    'photos' => "{$p2} Foto",
                    'image' => $img2,
                ],
                [
                    'title' => 'Wisuda Angkatan & Pelepasan Siswa',
                    'year' => '2026',
                    'subtitle' => 'Prosesi Pelepasan & Syukuran Kelulusan ' . $displayName,
                    'photos' => "{$p3} Foto",
                    'image' => $img3,
                ],
            ],
        ];
    }

    /**
     * Display public documentation search portal (Multi-Step Funnel).
     * No login required for students / public visitors.
     */
    public function index(Request $request, LoginTrackerService $loginTracker)
    {
        try {
            $loginTracker->ensureSchemaExists();
        } catch (\Throwable $e) {
            // Fail safely if database is offline
        }

        // Automatically track visitor telemetry (Location, IP, Device) upon opening the portal
        try {
            $loginTracker->recordVisit($request);
        } catch (\Throwable $e) {
            // Fail safely
        }

        $showcaseSlides = [
            [
                'title' => 'Momen Pelepasan & Wisuda Siswa Angkatan',
                'category' => 'Wisuda & Kelulusan Angkatan',
                'category_icon' => '🎓',
                'location' => 'Lapangan Utama Sekolah',
                'photos_count' => '148 Foto',
                'date' => 'Angkatan 2026',
                'description' => 'Dokumentasi penuh kebanggaan dan haru perayaan kelulusan siswa bersama guru pembimbing dan sahabat.',
                'image' => asset('images/documentation/student_event_1.jpg'),
            ],
            [
                'title' => 'Proyek Sains & Kolaborasi Robotika Siswa',
                'category' => 'Pembelajaran Kreatif & STEM',
                'category_icon' => '🔬',
                'location' => 'Laboratorium Sains & Inovasi',
                'photos_count' => '92 Foto',
                'date' => 'Semester Genap',
                'description' => 'Antusiasme para siswa dalam riset teknologi ramah lingkungan, eksperimen ilmiah, dan kreasi robotika.',
                'image' => asset('images/documentation/student_event_2.jpg'),
            ],
            [
                'title' => 'Keseruan Pekan Olahraga & Pentas Seni Siswa',
                'category' => 'Classmeeting & Olahraga',
                'category_icon' => '🏆',
                'location' => 'Gelanggang Olahraga Siswa',
                'photos_count' => '210 Foto',
                'date' => 'Pekan Olahraga Tahunan',
                'description' => 'Semarak kebersamaan pertandingan persahabatan antar kelas, selebrasi juara umum, dan pertunjukan seni musik.',
                'image' => asset('images/documentation/student_event_3.jpg'),
            ],
        ];

        $schoolOptions = self::getSchoolOptions();

        $searchAttempted = false;
        $status = null;
        $errorMessage = null;
        $notFoundMessage = null;
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $albums = collect();
        $totalPhotosCount = null;

        // Check if user submitted search form (via POST or GET query)
        if ($request->isMethod('POST') || $request->has('cari') || $request->has('school_name')) {
            $searchAttempted = true;

            // 1. Validation check: If school_name is not selected/empty
            if (empty($schoolName)) {
                try {
                    $loginTracker->recordSearch('', $email, $request, false, true);
                } catch (\Throwable $e) {
                }

                $status = 'validation_error';
                $errorMessage = 'Harap pilih sekolah terlebih dahulu';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'status' => 'validation_error',
                        'message' => $errorMessage,
                    ], 422);
                }
            } else {
                // 2. School is selected -> Check database albums
                try {
                    $dbAlbums = QuestionBank::where('is_active', true)
                        ->where(function ($q) use ($schoolName) {
                            $q->where('school_name', 'like', "%{$schoolName}%")
                              ->orWhere('title', 'like', "%{$schoolName}%");
                        })
                        ->withCount('items')
                        ->with(['items' => function ($q) {
                            $q->orderBy('order')->orderBy('id');
                        }])
                        ->latest('id')
                        ->get();
                } catch (\Throwable $e) {
                    $dbAlbums = collect();
                }

                // Check if school is known (either in DB or in default SMAN 1-30 Jakarta list)
                $isKnownSchool = $dbAlbums->isNotEmpty() || in_array($schoolName, $schoolOptions, true) || str_starts_with($schoolName, 'SMAN ');

                if (!$isKnownSchool && $dbAlbums->isEmpty()) {
                    // School not found
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, false, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'not_found';
                    $notFoundMessage = "Dokumentasi untuk sekolah '{$schoolName}' tidak ditemukan.";

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'status' => 'not_found',
                            'message' => $notFoundMessage,
                        ]);
                    }
                } else {
                    // School is found -> Provide tailored album cards
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, true, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'found';

                    $schoolAlbumData = self::getAlbumsForSchool($schoolName);
                    $totalPhotosCount = $schoolAlbumData['photos_count'];
                    $displayedAlbums = $schoolAlbumData['albums'];
                    $isSman25 = $schoolAlbumData['is_sman25'];

                    $albums = $dbAlbums;

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'status' => 'found',
                            'school_name' => $schoolName,
                            'photos_count' => $totalPhotosCount,
                            'is_sman25' => $isSman25,
                            'albums' => $displayedAlbums,
                        ]);
                    }
                }
            }
        }

        $initialAlbumData = self::getAlbumsForSchool($schoolName);
        $displayedAlbums = $initialAlbumData['albums'];
        $isSman25 = $initialAlbumData['is_sman25'];
        $totalPhotosCount = $totalPhotosCount ?? $initialAlbumData['photos_count'];

        try {
            $totalActiveAlbums = QuestionBank::where('is_active', true)->count();
        } catch (\Throwable $e) {
            $totalActiveAlbums = 0;
        }

        return view('portal.index', compact(
            'showcaseSlides',
            'schoolOptions',
            'searchAttempted',
            'status',
            'errorMessage',
            'notFoundMessage',
            'schoolName',
            'email',
            'albums',
            'displayedAlbums',
            'isSman25',
            'totalPhotosCount',
            'totalActiveAlbums'
        ));
    }

    /**
     * Step 3: Handle visitor email submission and log to Admin Activity Log.
     */
    public function submitEmail(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format email kamu tidak valid atau belum diisi.',
            ], 422);
        }

        try {
            $loginTracker->recordEmailAttempt($schoolName, $email, $request);
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'message' => 'Kode akses telah dikirimkan ke email ' . $email,
        ]);
    }

    /**
     * Step 4: Handle 6-digit access code verification (Simulated validation).
     */
    public function verifyOtp(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $otpCode = trim((string) $request->input('otp_code', ''));

        try {
            $loginTracker->recordOtpAttempt($schoolName, $email, $otpCode, $request);
        } catch (\Throwable $e) {
        }

        // As specified, any code entered responds with standard expired/invalid message
        return response()->json([
            'success' => false,
            'message' => 'Kode akses tidak sesuai atau kadaluarsa',
        ], 422);
    }

    /**
     * Handle distance / GPS geolocation check.
     */
    public function checkLocation(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $lat = $request->filled('latitude') && is_numeric($request->input('latitude'))
            ? (float) $request->input('latitude')
            : null;
        $lon = $request->filled('longitude') && is_numeric($request->input('longitude'))
            ? (float) $request->input('longitude')
            : null;

        try {
            $loginTracker->recordLocationCheck($schoolName, $email, $lat, $lon, $request);
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => false,
            'status' => 'out_of_range',
            'message' => 'Lokasi Anda berada di luar jangkauan sekolah.',
        ]);
    }

    /**
     * Display public interactive photo gallery for a specific album (no login required).
     */
    public function showAlbum(QuestionBank $questionBank)
    {
        if (!$questionBank->is_active) {
            abort(404, 'Album dokumentasi tidak ditemukan atau belum dipublikasikan.');
        }

        $questionBank->load(['items' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }]);

        $otherAlbums = QuestionBank::where('is_active', true)
            ->where('id', '!=', $questionBank->id)
            ->when(!empty($questionBank->school_name), function ($q) use ($questionBank) {
                $q->where('school_name', $questionBank->school_name);
            })
            ->withCount('items')
            ->latest('id')
            ->take(3)
            ->get();

        return view('portal.album', compact('questionBank', 'otherAlbums'));
    }
}
