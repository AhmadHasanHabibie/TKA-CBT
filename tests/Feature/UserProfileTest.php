<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_profile_page(): void
    {
        $response = $this->get(route('user.profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Peserta Uji Coba',
            'email' => 'peserta@tka.test',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('user.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profil Saya');
        $response->assertSee('Peserta Uji Coba');
        $response->assertSee('peserta@tka.test');
        $response->assertSee('Ganti Password Baru');
        $response->assertSee('Bebas Masuk Sandi Baru');
    }

    public function test_student_can_update_username_without_changing_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Awal',
            'password' => Hash::make('old_password_123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->put(route('user.profile.update'), [
            'name' => 'Nama Baru Peserta Keren',
        ]);

        $response->assertRedirect(route('user.profile.edit'));
        $response->assertSessionHas('success', 'Username berhasil diperbarui!');

        $user->refresh();
        $this->assertSame('Nama Baru Peserta Keren', $user->name);
        // Password remains unchanged
        $this->assertTrue(Hash::check('old_password_123', $user->password));
    }

    public function test_student_username_validation_requires_minimum_length(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->put(route('user.profile.update'), [
            'name' => 'A', // too short
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_student_can_update_password_directly_without_old_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Siswa Mandiri',
            'password' => Hash::make('old_forgotten_pass'),
            'role' => 'user',
        ]);

        // No 'current_password' sent! Only new password & confirmation.
        $response = $this->actingAs($user)->put(route('user.profile.update-password'), [
            'password' => 'new_secret_pass_789',
            'password_confirmation' => 'new_secret_pass_789',
        ]);

        $response->assertRedirect(route('user.profile.edit'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new_secret_pass_789', $user->password));
        $this->assertFalse(Hash::check('old_forgotten_pass', $user->password));
    }

    public function test_password_update_fails_if_confirmation_does_not_match(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->put(route('user.profile.update-password'), [
            'password' => 'mypassword123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_update_fails_if_password_too_short(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->put(route('user.profile.update-password'), [
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_newly_updated_password_can_be_used_for_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login_test@tka.test',
            'password' => Hash::make('first_pass_123'),
            'role' => 'user',
        ]);

        // Update password via profile
        $this->actingAs($user)->put(route('user.profile.update-password'), [
            'password' => 'second_pass_456',
            'password_confirmation' => 'second_pass_456',
        ]);

        // Logout
        $this->post(route('logout'));

        // Attempt login with new password
        $loginResponse = $this->post(route('login.post'), [
            'email' => 'login_test@tka.test',
            'password' => 'second_pass_456',
        ]);

        $loginResponse->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_admin_can_access_and_update_admin_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Awal',
            'role' => 'admin',
        ]);

        // View admin profile
        $response = $this->actingAs($admin)->get(route('admin.profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Profil Administrator');

        // Update name
        $updateNameResponse = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Superadmin Master',
        ]);
        $updateNameResponse->assertRedirect(route('admin.profile.edit'));
        $admin->refresh();
        $this->assertSame('Superadmin Master', $admin->name);

        // Update password directly
        $updatePassResponse = $this->actingAs($admin)->put(route('admin.profile.update-password'), [
            'password' => 'admin_brand_new_pass_2026',
            'password_confirmation' => 'admin_brand_new_pass_2026',
        ]);
        $updatePassResponse->assertRedirect(route('admin.profile.edit'));
        $admin->refresh();
        $this->assertTrue(Hash::check('admin_brand_new_pass_2026', $admin->password));
    }
}
