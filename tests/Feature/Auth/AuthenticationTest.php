<?php

namespace Tests\Feature\Auth;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered_and_tracks_visitor_without_login(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'X-Forwarded-For' => '114.122.10.55',
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Galeri');
        $response->assertDontSee('Verifikasi PIN Admin');

$this->assertDatabaseHas('login_logs', [
            'user_id' => null,
            'event_type' => 'visit',
            'ip_address' => '114.122.10.55',
            'browser' => 'Apple Safari 17.2',
        ]);

$telemetryResp = $this->postJson(route('telemetry.track'), [
            'client_ip' => '114.122.10.55',
            'client_location' => 'Bandung, Jawa Barat, Indonesia',
            'client_latitude' => -6.9175,
            'client_longitude' => 107.6191,
        ]);

        $telemetryResp->assertStatus(200)->assertJson([
            'success' => true,
            'ip' => '114.122.10.55',
            'location' => 'Bandung, Jawa Barat, Indonesia',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'user_id' => null,
            'ip_address' => '114.122.10.55',
            'location' => 'Bandung, Jawa Barat, Indonesia',
        ]);
    }

    public function test_users_can_authenticate_and_login_info_is_recorded(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'client_ip' => '103.10.20.30',
            'client_location' => 'Jakarta, DKI Jakarta, Indonesia',
            'client_latitude' => -6.2088,
            'client_longitude' => 106.8456,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('user.dashboard'));

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
            'ip_address' => '103.10.20.30',
            'location' => 'Jakarta, DKI Jakarta, Indonesia',
            'browser' => 'Google Chrome 131.0',
            'os' => 'Windows 10/11',
        ]);

        $user->refresh();
        $this->assertEquals('103.10.20.30', $user->last_login_ip);
        $this->assertEquals('Jakarta, DKI Jakarta, Indonesia', $user->last_login_location);
        $this->assertEquals('Google Chrome 131.0', $user->last_login_browser);
    }

    public function test_admin_can_login_directly_without_two_step_pin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));

$dashResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);

        $logsResponse = $this->actingAs($admin)->get(route('admin.login-logs.index'));
        $logsResponse->assertStatus(200);
        $logsResponse->assertSee('Log Lokasi, IP Address & Browser User', false);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'client_ip' => '180.252.10.20',
        ]);

        $this->assertGuest();

$this->assertDatabaseHas('login_logs', [
            'event_type' => 'failed_login',
            'attempted_email' => $user->email,
            'ip_address' => '180.252.10.20',
        ]);
    }

    public function test_failed_login_with_non_existent_email_records_attempted_email(): void
    {
        $this->post('/login', [
            'email' => 'pengguna_palsu@sekolah.sch.id',
            'password' => 'secret123',
            'client_ip' => '180.252.10.21',
        ]);

        $this->assertGuest();

        $this->assertDatabaseHas('login_logs', [
            'event_type' => 'failed_login',
            'attempted_email' => 'pengguna_palsu@sekolah.sch.id',
            'user_id' => null,
            'ip_address' => '180.252.10.21',
        ]);
    }

    public function test_login_screen_auto_creates_login_logs_table_if_not_migrated_on_hosting(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('login_logs');
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('login_logs'));

        $response = $this->get('/login');

        $response->assertStatus(200);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('login_logs'));
        LoginLog::query()->delete();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_cloudflare_true_ip_priority_skips_private_proxy_ips(): void
    {
        $response = $this->withHeaders([
            'CF-Connecting-IP' => '36.72.210.45',
            'True-Client-IP' => '103.20.30.40',
            'X-Real-IP' => '10.0.0.1',
            'X-Forwarded-For' => '192.168.1.1, 36.72.210.45',
        ])->get('/login');

        $response->assertStatus(200);
        $this->assertDatabaseHas('login_logs', [
            'ip_address' => '36.72.210.45',
        ]);
    }

    public function test_passive_diagnostic_telemetry_and_device_correlation_and_vpn_detection(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'ip-api.com/*' => \Illuminate\Support\Facades\Http::response([
                'status' => 'success',
                'country' => 'Singapore',
                'countryCode' => 'SG',
                'regionName' => 'Central Singapore',
                'city' => 'Singapore',
                'district' => 'Downtown Core',
                'lat' => 1.2897,
                'lon' => 103.8501,
                'isp' => 'DigitalOcean LLC',
                'org' => 'DigitalOcean Cloud',
                'as' => 'AS14061 DigitalOcean, LLC',
                'mobile' => true,
                'proxy' => true,
                'hosting' => true,
                'timezone' => 'Asia/Singapore',
                'query' => '128.199.100.25',
            ], 200),
        ]);

        $student = User::factory()->create([
            'name' => 'Student One',
            'email' => 'student1@example.com',
            'role' => 'user',
        ]);

        LoginLog::create([
            'user_id' => $student->id,
            'event_type' => 'login',
            'ip_address' => '114.122.10.55',
            'location' => 'Jakarta, Indonesia',
            'device_fingerprint' => '8f4e2a91c0b3d7e5',
            'logged_in_at' => now()->subHour(),
        ]);

        $telemetryResp = $this->withHeaders([
            'CF-Connecting-IP' => '128.199.100.25',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36',
        ])->postJson(route('telemetry.track'), [
            'device_fingerprint' => '8f4e2a91c0b3d7e5',
            'device_model' => 'SM-A546E',
            'gpu_renderer' => 'Mali-G68 MP5',
            'cpu_cores' => 8,
            'ram_gb' => 8,
            'screen_resolution' => '1080x2340',
            'battery_level' => 84,
            'battery_charging' => true,
            'network_type' => '4g',
            'network_downlink' => 24.5,
            'network_rtt' => 45,
            'client_timezone' => 'Asia/Jakarta',
        ]);

        $telemetryResp->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('login_logs', [
            'user_id' => null,
            'ip_address' => '128.199.100.25',
            'district' => 'Downtown Core',
            'isp' => 'DigitalOcean LLC',
            'is_mobile_network' => 1,
            'is_proxy' => 1,
            'is_hosting' => 1,
            'is_vpn_suspected' => 1,
            'ip_timezone' => 'Asia/Singapore',
            'client_timezone' => 'Asia/Jakarta',
            'device_fingerprint' => '8f4e2a91c0b3d7e5',
            'device_model' => 'SM-A546E',
            'gpu_renderer' => 'Mali-G68 MP5',
            'cpu_cores' => 8,
            'screen_resolution' => '1080x2340',
            'battery_level' => 84,
            'battery_charging' => 1,
            'network_type' => '4g',
            'network_rtt' => 45,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $adminPage = $this->actingAs($admin)->get(route('admin.login-logs.index'));
        $adminPage->assertStatus(200);
        $adminPage->assertSee('Perangkat terasosiasi:');
        $adminPage->assertSee('Student One');
        $adminPage->assertSee('Kec. Downtown Core');
        $adminPage->assertSee('DigitalOcean LLC');
        $adminPage->assertSee('VPN / Proxy Terdeteksi');
        $adminPage->assertSee('Seluler / Hotspot');
        $adminPage->assertSee('TZ Mismatch: Asia/Jakarta');
        $adminPage->assertSee('SM-A546E');
        $adminPage->assertSee('GPU: Mali-G68 MP5');
        $adminPage->assertSee('8 Core');
        $adminPage->assertSee('8GB RAM');
        $adminPage->assertSee('1080x2340');
        $adminPage->assertSee('ID: 8F4E2A91C0');
        $adminPage->assertSee('84%');
        $adminPage->assertSee('⚡ Dicas');
        $adminPage->assertSee('4G • 24.5 Mbps • 45 ms');
    }
}
