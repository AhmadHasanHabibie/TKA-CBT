<?php

namespace App\Http\Controllers;

use App\Services\LoginTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    public function showLoginForm(Request $request, LoginTrackerService $loginTracker)
    {
        
        try {
            $loginTracker->recordVisit($request);
        } catch (\Throwable $e) {
            
        }

        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

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

try {
                $loginTracker->recordLogin($user, $request);
            } catch (\Throwable $e) {
                
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

try {
            $attemptedEmail = (string) $request->input('email', '');
            if (!empty($attemptedEmail)) {
                $loginTracker->recordFailedLogin($attemptedEmail, $request);
            }
        } catch (\Throwable $e) {
            
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

public function logout(Request $request)
    {
        $request->session()->forget(['admin_pin_verified', 'admin_pin_verified_at', 'visitor_log_id']);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }
}
