<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('user.dashboard'));
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

    public function test_admin_login_without_pin_redirects_to_pin_verification(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('login'));
        $this->assertFalse(session('admin_pin_verified', false));

        // Follow redirect to /login and verify PIN screen is shown without leaking 252009
        $followResponse = $this->get(route('login'));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('Verifikasi PIN Admin');
        $followResponse->assertDontSee('252009');
    }

    public function test_initial_login_screen_does_not_show_pin_field(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
        $response->assertDontSee('Verifikasi PIN Admin');
        $response->assertDontSee('252009');
    }

    public function test_unverified_admin_cannot_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_pin_verified' => false])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_pin_verification_fails_with_invalid_pin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_pin_verified' => false])
            ->post(route('login.post'), [
                'pin' => '123456',
            ]);

        $response->assertSessionHasErrors('pin');
        $this->assertFalse(session('admin_pin_verified', false));
    }

    public function test_admin_pin_verification_succeeds_with_pin_252009(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_pin_verified' => false])
            ->post(route('login.post'), [
                'pin' => '252009',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_pin_verified'));
    }

    public function test_admin_can_login_directly_with_correct_pin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'pin' => '252009',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_pin_verified'));
    }

}

