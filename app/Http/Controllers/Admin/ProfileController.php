<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.profile.edit', [
            'user' => $user,
        ]);
    }

public function updateProfile(Request $request): RedirectResponse
    {
        
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ], [
            'name.required' => 'Nama administrator wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'name.max' => 'Nama maksimal 100 karakter.',
        ]);

        $user->name = trim($validated['name']);
        $user->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Nama administrator berhasil diperbarui!');
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

        return redirect()->route('admin.profile.edit')->with('success', 'Password administrator berhasil diperbarui!');
    }
}
