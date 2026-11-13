<?php

namespace App\Http\Controllers;

use App\Services\LoginTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form or redirect authenticated users.
     * Immediately records visitor IP, Location, and Browser upon clicking the link (even before login).
     */
    public function showLoginForm(Request $request, LoginTrackerService $loginTracker)
    {
        // Safely record visitor telemetry when they open/click the link
        try {
            $loginTracker->recordVisit($request);
        } catch (\Throwable $e) {
            // Never block login page rendering
        }

        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Real-time telemetry endpoint called automatically when any visitor opens the link (no login required).
     */
    public function trackVisitor(Request $request, LoginTrackerService $loginTracker)
    {
        $log = $loginTracker->recordVisit($request);

        return response()->json([
            'success' => true,
            'log_id' => $log?->id,
            'ip' => $log?->ip_address,
            'location' => $log?->location,
        ]);
    }

    /**
     * Handle an authentication attempt (Direct login without 2-step verification for Admin & User).
     */
    public function login(Request $request, LoginTrackerService $loginTracker)
    {
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

            $user = Auth::user();

            // Record user login activity and link pre-login visit record
            try {
                $loginTracker->recordLogin($user, $request);
            } catch (\Throwable $e) {
                // Never block authentication redirect
            }

            if ($user->isAdmin()) {
                session([
                    'admin_pin_verified' => true,
                    'admin_pin_verified_at' => now(),
                ]);

                $intended = session()->get('url.intended');
                if ($intended && str_contains($intended, '/user')) {
                    session()->forget('url.intended');
                }

                return redirect()->intended(route('admin.dashboard'));
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
        $request->session()->forget(['admin_pin_verified', 'admin_pin_verified_at', 'visitor_log_id']);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }
}
