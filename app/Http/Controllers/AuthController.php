<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form or redirect authenticated users.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                if (!session('admin_pin_verified')) {
                    return view('auth.login', [
                        'adminDetected' => true,
                    ]);
                }
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('user.dashboard');
        }

        return view('auth.login', [
            'adminDetected' => false,
        ]);
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        // If already logged in as admin needing PIN verification
        if (Auth::check() && Auth::user()->isAdmin() && !session('admin_pin_verified')) {
            $request->validate([
                'pin' => ['required', 'string'],
            ], [
                'pin.required' => 'PIN keamanan wajib diisi.',
            ]);

            $expectedPin = (string) config('auth.admin_pin', '252009');
            if (trim((string) $request->pin) === $expectedPin) {
                session([
                    'admin_pin_verified' => true,
                    'admin_pin_verified_at' => now(),
                ]);

                $intended = session()->get('url.intended');
                if ($intended && !str_contains($intended, '/admin')) {
                    session()->forget('url.intended');
                    $intended = null;
                }

                return redirect()->intended(route('admin.dashboard'));
            }

            return back()->withErrors([
                'pin' => 'PIN keamanan yang Anda masukkan salah. Silakan coba lagi.',
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                $intended = session()->get('url.intended');
                if ($intended && str_contains($intended, '/user')) {
                    session()->forget('url.intended');
                }

                // If PIN was passed along directly
                $expectedPin = (string) config('auth.admin_pin', '252009');
                if ($request->filled('pin') && trim((string) $request->pin) === $expectedPin) {
                    session([
                        'admin_pin_verified' => true,
                        'admin_pin_verified_at' => now(),
                    ]);
                    return redirect()->intended(route('admin.dashboard'));
                }

                // First time clicking "Masuk ke Sistem": Detected as Admin!
                session()->forget('admin_pin_verified');
                return redirect()->route('login');
            }

            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/admin')) {
                session()->forget('url.intended');
            }
            return redirect()->intended(route('user.dashboard'));
        }


        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('admin_pin_verified');
        $request->session()->forget('admin_pin_verified_at');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }

}
