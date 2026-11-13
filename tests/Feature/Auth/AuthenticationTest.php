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
        $response->assertSee('Masuk ke Sistem');
        $response->assertDontSee('Verifikasi PIN Admin');

        // Visitor IP and Browser are recorded immediately upon clicking the link (without logging in)
        $this->assertDatabaseHas('login_logs', [
            'user_id' => null,
            'event_type' => 'visit',
            'ip_address' => '114.122.10.55',
            'browser' => 'Apple Safari 17.2',
        ]);

        // Instant client telemetry updates location & GPS coordinates without requiring login
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

        // Admin can immediately access admin dashboard and login logs
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
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
