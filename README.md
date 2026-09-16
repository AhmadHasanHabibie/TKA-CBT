# TKA Computer-Based Test (CBT) Application

A high-performance, enterprise-grade, server-authoritative Computer-Based Test (CBT) web application built for Academic Competence Testing (Tes Kompetensi Akademik / TKA) using **Laravel 10**, **SQLite**, **Tailwind CSS**, **Alpine.js**, and **`smalot/pdfparser`**.

---

## 🚀 Fitur Utama

### 1. Peran Administrator (`/admin`)
- **Dashboard KPI**: Statistik real-time total peserta, subtest aktif, total pengerjaan ujian, rata-rata skor global, serta tabel 5 aktivitas ujian terbaru.
- **Kelola Pengguna**: CRUD akun peserta dan admin (dilengkapi perlindungan anti-hapus akun sendiri). Tidak ada registrasi publik untuk menjamin integritas peserta.
- **Kelola Subtest**: Buat subtest baru dengan durasi kustom (menit), deskripsi petunjuk, dan toggle aktif/nonaktif.
- **PDF Parser & Staging Area (Core Engine)**:
  - Ekstraksi teks dari file PDF soal menggunakan `smalot/pdfparser` berdasarkan pola baku SOP TKA (`SOAL`, `A-E`, `KUNCI:`, `PEMBAHASAN:`).
  - Mendukung format soal 4 pilihan (A–D) maupun 5 pilihan (A–E).
  - Validasi otomatis: jika kunci jawaban tidak cocok atau pembahasan tidak ditemukan, soal ditandai dengan badge peringatan merah (*"Perlu dicek manual"*) tanpa membatalkan seluruh proses.
  - Form staging interaktif untuk mengedit teks soal, opsi jawaban, mengubah kunci via radio button, dan menyunting pembahasan sebelum publikasi.
  - Komitmen database menggunakan `DB::transaction()` dengan pembaruan otomatis kolom `total_questions`.

### 2. Peran Peserta / Siswa (`/user`)
- **Dashboard Peserta**: Kartu status tiap subtest (*Belum Dikerjakan*, *Sedang Berlangsung*, atau *Selesai — Skor: XX*), ringkasan kemajuan subtest selesai, dan rata-rata skor personal.
- **Papan Ujian (Exam Board)**:
  - **Server-Authoritative Timer**: Waktu pengerjaan berpatokan pada `ends_at` di server (`started_at + duration_minutes`). Countdown visual Alpine.js di client hanya representasi antarmuka. Server menolak penyimpanan jawaban dan otomatis menilai jika waktu habis.
  - **State Recovery**: Peserta yang merefresh atau menutup browser saat ujian berlangsung dapat kembali melanjutkan ujian tanpa mereset waktu ataupun jawaban yang tersimpan.
  - **Auto-Save Cepat**: Setiap klik opsi pilihan atau tombol ragu-ragu langsung mengirim AJAX native `fetch()` ke endpoint `/user/exam/{session}/answer` untuk upsert ke tabel `user_answers`.
  - **Navigasi Grid Nomor Soal**: Kode warna intuitif (Abu-abu: Belum dijawab, Indigo: Sudah dijawab, Amber: Ragu-ragu, Border tebal: Soal aktif).
  - **Anti-Cheat Ringan**: Pendeteksian `window.onblur` dan `visibilitychange` mencatat `tab_violation_count` ke server dan menampilkan notifikasi peringatan integritas.
- **Hasil & Pembahasan Ujian**:
  - Tampilan skor besar terstandar (0–100) dan ringkasan soal benar/salah/kosong.
  - Evaluasi per soal dengan penandaan warna hijau (kunci benar) dan merah (pilihan salah/pilihan peserta).
  - Penjelasan lengkap (*PEMBAHASAN*) untuk setiap butir soal.
  - Filter interaktif: *Tampilkan Semua Soal* vs *Hanya yang Salah / Kosong*.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 10.50 (PHP 8.1+), Monolith Architecture
- **Database**: SQLite (`database/database.sqlite`), migrasi kompatibel dengan MySQL
- **Autentikasi**: Manual session guard via `Auth` facade (ringan, aman, tanpa dependensi Breeze)
- **Frontend**: Blade Templates, Tailwind CSS (via Vite), Alpine.js, Vanilla JS `fetch()`
- **PDF Engine**: `smalot/pdfparser` v2.12

---

## 📋 Format Baku Dokumen PDF Soal (SOP Template)

Dokumen PDF yang diunggah harus mengikuti format struktur berikut:

```text
SOAL [nomor]
[teks pertanyaan, dapat berupa multi-baris]
A. [opsi jawaban A]
B. [opsi jawaban B]
C. [opsi jawaban C]
D. [opsi jawaban D]
E. [opsi jawaban E — opsional untuk soal 4 opsi]
KUNCI: [A/B/C/D/E]
PEMBAHASAN: [teks pembahasan atau rumus cara penyelesaian, dapat multi-baris]
```

*Contoh file PDF yang valid sudah tersedia di: `storage/app/contoh_soal_tka.pdf`.*

---

## ⚡ Panduan Instalasi & Menjalankan Aplikasi

### 1. Clone atau Buka Folder Project
```bash
cd c:\laragon\www\TKA-CBT
```

### 2. Konfigurasi Environment (`.env`)
Pastikan koneksi database di file `.env` diatur ke SQLite:
```ini
DB_CONNECTION=sqlite
```

### 3. Migrasi Database & Seeder
Jalankan migrasi bersih beserta seed akun demo dan subtest percontohan:
```bash
php artisan migrate:fresh --seed
```

### 4. Build Aset Frontend (Tailwind CSS & Vite)
```bash
npm install
npm run build
```

### 5. Jalankan Web Server
```bash
php artisan serve --port=8000
```
Buka peramban (browser) di: **`http://127.0.0.1:8000`**

---

## 🔐 Akun Demo untuk Pengujian

| Peran | Email | Kata Sandi | Akses Halaman |
|---|---|---|---|
| **Administrator** | `admin@tka.test` | `password` | `/admin/dashboard` |
| **Peserta 1** | `budi@tka.test` | `password` | `/user/dashboard` |
| **Peserta 2** | `siti@tka.test` | `password` | `/user/dashboard` |
| **Peserta 3** | `ahmad@tka.test` | `password` | `/user/dashboard` |

---

## 🧪 Menjalankan Automated Tests (Feature E2E)

Aplikasi dilengkapi dengan suite pengujian otomatis komprehensif yang menguji seluruh alur autentikasi, role guards, CRUD admin, staging PDF, auto-save ujian, penilaian skor, dan batas kedaluwarsa waktu server:

```bash
php artisan test --filter=CbtEndToEndTest
```

---

## ⚠️ Catatan Batasan Sistem

- **Anti-Cheat**: Sistem ini mengimplementasikan pencatatan perpindahan tab (`tab_violation_count`) dan pencegahan pintasan salin-tempel biasa. Fitur ini berfungsi sebagai audit integritas ujian dasar, bukan proctoring video penuh (seperti perekaman webcam atau browser lockdown berbasis native OS).
