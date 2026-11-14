<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    
    public function edit(): View
    {
        
        $user = Auth::user();

        $totalAlbums = 0;
        $totalPhotos = 0;
        try {
            $totalAlbums = QuestionBank::where('is_active', true)->count();
            $totalPhotos = QuestionBankItem::whereHas('questionBank', fn($q) => $q->where('is_active', true))->count();
        } catch (\Throwable $e) {
        }

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
            'totalAlbums' => $totalAlbums,
            'totalPhotos' => $totalPhotos,
            'completedSessions' => $completedSessions,
            'ongoingSessions' => $ongoingSessions,
            'avgScore' => (float) $avgScore,
        ]);
    }

public function updateProfile(Request $request): RedirectResponse
    {
        
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

public function updatePassword(Request $request): RedirectResponse
    {
        
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
