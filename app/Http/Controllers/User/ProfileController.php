<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile settings page.
     */
    public function edit(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $completedSessions = $user->examSessions()
            ->where('status', 'finished')
            ->count();

        $ongoingSessions = $user->examSessions()
            ->where('status', 'ongoing')
            ->count();

        $avgScore = $user->examSessions()
            ->where('status', 'finished')
            ->avg('score') ?? 0;

        return view('user.profile.edit', [
            'user' => $user,
            'completedSessions' => $completedSessions,
            'ongoingSessions' => $ongoingSessions,
            'avgScore' => (float) $avgScore,
        ]);
    }

    /**
     * Update the user's username / name.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ], [
            'name.required' => 'Username / Nama lengkap wajib diisi.',
            'name.min' => 'Username / Nama minimal 2 karakter.',
            'name.max' => 'Username / Nama maksimal 100 karakter.',
        ]);

        $user->name = trim($validated['name']);
        $user->save();

        return redirect()->route('user.profile.edit')->with('success', 'Username berhasil diperbarui!');
    }

    /**
     * Update the user's password directly without requiring the old password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('user.profile.edit')->with('success', 'Password berhasil diperbarui! Anda dapat langsung menggunakannya untuk login.');
    }
}
