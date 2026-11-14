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
        $response->assertSee('SMAN 1 Jakarta');
        $response->assertSee('SMAN 30 Jakarta');
        $response->assertSee('Cari Dokumentasi');

$response->assertDontSee('/dokumentasi-siswa/login/admin');
        $response->assertDontSee('Masuk Admin');
        $response->assertDontSee('Login Admin');
    }

    public function test_empty_search_shows_validation_error_and_logs_to_admin(): void
    {
        
        $response = $this->post('/cari-dokumentasi', [
            'school_name' => '',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Harap pilih sekolah terlebih dahulu');

$this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_empty',
        ]);
    }

    public function test_search_not_found_displays_proper_message_and_logs_to_admin(): void
    {
        $response = $this->post('/cari-dokumentasi', [
            'school_name' => 'Sekolah Swasta Tidak Terdaftar 999',
        ]);

        $response->assertStatus(200);
        $response->assertSee('tidak ditemukan');

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_not_found',
            'school_name' => 'Sekolah Swasta Tidak Terdaftar 999',
        ]);
    }

    public function test_search_found_returns_album_and_logs_to_admin(): void
    {
        $album = QuestionBank::create([
            'title' => 'Wisuda Angkatan 2026',
            'slug' => 'wisuda-angkatan-2026',
            'school_name' => 'SMAN 1 Jakarta',
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
            'school_name' => 'SMAN 1 Jakarta',
        ]);

        $response->assertStatus(200);
        $response->assertSee('SMAN 1 Jakarta');
        $response->assertSee('Klik untuk membuka album');

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_found',
            'school_name' => 'SMAN 1 Jakarta',
        ]);
    }

    public function test_sman_25_jakarta_displays_237_photos_and_six_specific_2026_albums(): void
    {
        $response = $this->post('/cari-dokumentasi', [
            'school_name' => 'SMAN 25 Jakarta',
        ]);

        $response->assertStatus(200);
        $response->assertSee('237');
        $response->assertSee('SMAN 25 Jakarta');
        $response->assertSee('Bulan Bahasa');
        $response->assertSee('MPLS');
        $response->assertSee('Class Meeting');
        $response->assertSee('LDKS');
        $response->assertSee('17 Agustus');
        $response->assertSee('Hari Kartini');
        $response->assertSee('2026');

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'search_found',
            'school_name' => 'SMAN 25 Jakarta',
        ]);

$jsonResponse = $this->postJson('/cari-dokumentasi', [
            'school_name' => 'SMAN 25 Jakarta',
        ]);

        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson([
            'success' => true,
            'status' => 'found',
            'school_name' => 'SMAN 25 Jakarta',
            'photos_count' => 237,
            'is_sman25' => true,
        ]);
        $this->assertCount(6, $jsonResponse->json('albums'));
    }

    public function test_step_3_submit_email_logs_to_admin_and_returns_success(): void
    {
        $response = $this->postJson('/cari-dokumentasi/submit-email', [
            'school_name' => 'SMAN 1 Jakarta',
            'email' => 'siswa@sman1jakarta.sch.id',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'email' => 'siswa@sman1jakarta.sch.id',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'email_input',
            'school_name' => 'SMAN 1 Jakarta',
            'attempted_email' => 'siswa@sman1jakarta.sch.id',
        ]);
    }

    public function test_step_4_verify_otp_returns_error_and_logs_attempt_to_admin(): void
    {
        $response = $this->postJson('/cari-dokumentasi/verify-otp', [
            'school_name' => 'SMAN 1 Jakarta',
            'email' => 'siswa@sman1jakarta.sch.id',
            'otp_code' => '987654',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode akses tidak sesuai atau kadaluarsa',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'otp_attempt',
            'school_name' => 'SMAN 1 Jakarta',
            'attempted_email' => 'siswa@sman1jakarta.sch.id',
        ]);
    }

    public function test_geolocation_check_logs_coordinates_and_returns_out_of_range(): void
    {
        $response = $this->postJson('/cari-dokumentasi/check-location', [
            'school_name' => 'SMAN 1 Jakarta',
            'email' => 'siswa@sman1jakarta.sch.id',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'status' => 'out_of_range',
            'message' => 'Lokasi Anda berada di luar jangkauan sekolah.',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'location_check',
            'school_name' => 'SMAN 1 Jakarta',
            'attempted_email' => 'siswa@sman1jakarta.sch.id',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
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

    public function test_schools_have_distinct_documentation_profiles_and_sman25_is_preserved(): void
    {
        $schoolsToTest = [
            'SMAN 1 Jakarta',
            'SMAN 2 Jakarta',
            'SMAN 3 Jakarta',
            'SMAN 8 Jakarta',
            'SMAN 25 Jakarta',
            'SMAN 28 Jakarta',
            'SMAN 30 Jakarta',
        ];

        $photoCounts = [];
        $firstAlbumTitles = [];

        foreach ($schoolsToTest as $school) {
            $response = $this->postJson('/cari-dokumentasi', ['school_name' => $school]);
            $response->assertStatus(200);
            $response->assertJson(['success' => true, 'status' => 'found']);

            $data = $response->json();
            $photoCounts[$school] = $data['photos_count'];
            $firstAlbumTitles[$school] = $data['albums'][0]['title'];
        }

$this->assertEquals(237, $photoCounts['SMAN 25 Jakarta']);
        $sman25Res = $this->postJson('/cari-dokumentasi', ['school_name' => 'SMAN 25 Jakarta']);
        $this->assertCount(6, $sman25Res->json('albums'));
        $this->assertTrue($sman25Res->json('is_sman25'));

$this->assertNotEquals($firstAlbumTitles['SMAN 1 Jakarta'], $firstAlbumTitles['SMAN 2 Jakarta']);
        $this->assertNotEquals($firstAlbumTitles['SMAN 2 Jakarta'], $firstAlbumTitles['SMAN 3 Jakarta']);
        $this->assertNotEquals($firstAlbumTitles['SMAN 3 Jakarta'], $firstAlbumTitles['SMAN 8 Jakarta']);
        $this->assertNotEquals($firstAlbumTitles['SMAN 8 Jakarta'], $firstAlbumTitles['SMAN 25 Jakarta']);
        $this->assertNotEquals($firstAlbumTitles['SMAN 28 Jakarta'], $firstAlbumTitles['SMAN 30 Jakarta']);

$this->assertCount(count($schoolsToTest), array_unique($photoCounts));
    }

    public function test_school_options_deduplicates_case_insensitive_variants_like_sman_25_jakarta(): void
    {
        
        QuestionBank::create([
            'title' => 'Dokumentasi Khusus 2026',
            'slug' => 'dokumentasi-khusus-sman-25-jakarta-caps',
            'school_name' => 'SMAN 25 JAKARTA',
            'is_active' => true,
        ]);

        $options = \App\Http\Controllers\PortalController::getSchoolOptions();

$sman25Matches = array_filter($options, function ($opt) {
            return (bool) preg_match('/sman\s*25\s*jakarta/i', $opt);
        });

        $this->assertCount(1, $sman25Matches, 'SMAN 25 Jakarta must only appear ONCE in school options dropdown');
        $this->assertEquals('SMAN 25 Jakarta', array_values($sman25Matches)[0]);

$response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();
        preg_match_all('/<option[^>]*>\s*SMAN 25 Jakarta\s*<\/option>/i', $content, $matches);
        $this->assertCount(1, $matches[0], 'Select dropdown should contain SMAN 25 Jakarta option exactly once');
        $this->assertStringNotContainsString('SMAN 25 JAKARTA', $content);
    }
}
