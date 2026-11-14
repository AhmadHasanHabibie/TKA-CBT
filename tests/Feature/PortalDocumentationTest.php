<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_portal_is_accessible_without_login_and_has_no_admin_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Portal Dokumentasi Foto Siswa');
        $response->assertSee('Nama Sekolah');
        $response->assertSee('Email Kamu');
        $response->assertSee('Cari Dokumentasi');

        // Confirm there is NO admin login button or link on the public portal
        $response->assertDontSee('/dokumentasi-siswa/login/admin');
        $response->assertDontSee('Masuk Admin');
        $response->assertDontSee('Login Admin');
    }

    public function test_empty_search_shows_exact_validation_error_and_logs_to_admin(): void
    {
        // 1. Submit empty form
        $response = $this->post('/cari-dokumentasi', [
            'school_name' => '',
            'email' => '',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Harap Isi Data terlebih dahulu');

        // Verify logged in admin login_logs
        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_empty',
        ]);

        // 2. Submit partial input (e.g. only school name typed, email empty)
        $response2 = $this->post('/cari-dokumentasi', [
            'school_name' => 'SMK Bina Informatika',
            'email' => '',
        ]);

        $response2->assertStatus(200);
        $response2->assertSee('Harap Isi Data terlebih dahulu');

        // Verify whatever user typed was captured in log!
        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_empty',
            'school_name' => 'SMK Bina Informatika',
        ]);
    }

    public function test_search_not_found_displays_proper_message_and_logs_to_admin(): void
    {
        $response = $this->post('/cari-dokumentasi', [
            'school_name' => 'Sekolah Tidak Ada 999',
            'email' => 'siswa@sekolah.com',
        ]);

        $response->assertStatus(200);
        $response->assertSee('tidak ditemukan');

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_not_found',
            'school_name' => 'Sekolah Tidak Ada 999',
            'attempted_email' => 'siswa@sekolah.com',
        ]);
    }

    public function test_search_found_returns_album_and_logs_to_admin(): void
    {
        $album = QuestionBank::create([
            'title' => 'Wisuda Angkatan 2026',
            'slug' => 'wisuda-angkatan-2026',
            'school_name' => 'SMA Garuda Mandiri',
            'category' => 'Wisuda & Kelulusan',
            'is_active' => true,
        ]);

        QuestionBankItem::create([
            'question_bank_id' => $album->id,
            'title' => 'Foto Pelepasan Siswa',
            'image' => 'images/documentation/student_event_1.jpg',
            'order' => 1,
        ]);

        $response = $this->post('/cari-dokumentasi', [
            'school_name' => 'SMA Garuda Mandiri',
            'email' => 'alumni@garuda.sch.id',
        ]);

        $response->assertStatus(200);
        $response->assertSee('SMA Garuda Mandiri');
        $response->assertSee('Wisuda Angkatan 2026');

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_found',
            'school_name' => 'SMA Garuda Mandiri',
            'attempted_email' => 'alumni@garuda.sch.id',
        ]);
    }

    public function test_public_can_view_album_photos_without_login(): void
    {
        $album = QuestionBank::create([
            'title' => 'Pekan Olahraga Siswa',
            'slug' => 'pekan-olahraga-siswa',
            'school_name' => 'SMA Negeri 1 Prestasi',
            'category' => 'Olahraga',
            'is_active' => true,
        ]);

        $item = QuestionBankItem::create([
            'question_bank_id' => $album->id,
            'title' => 'Pertandingan Basket Final',
            'image' => 'images/documentation/student_event_3.jpg',
            'order' => 1,
        ]);

        $response = $this->get('/dokumentasi/' . $album->slug);

        $response->assertStatus(200);
        $response->assertSee('Pekan Olahraga Siswa');
        $response->assertSee('SMA Negeri 1 Prestasi');
        $response->assertSee('Pertandingan Basket Final');
    }

    public function test_secret_admin_login_doorway_is_accessible_via_direct_url(): void
    {
        $response = $this->get('/dokumentasi-siswa/login/admin');

        $response->assertStatus(200);
        $response->assertSee('Portal Dokumentasi Foto Siswa');
        $response->assertSee('Masuk');
    }
}
