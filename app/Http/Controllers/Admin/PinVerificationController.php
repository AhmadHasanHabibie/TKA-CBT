<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinVerificationController extends Controller
{
    /**
     * Show the administrator PIN verification form.
     */
    public function show()
    {
        // If already verified in this session, head straight to admin dashboard
        if (session('admin_pin_verified')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-pin');
    }

    /**
     * Verify the administrator PIN submission.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string'],
        ], [
            'pin.required' => 'PIN keamanan wajib diisi.',
        ]);

        $expectedPin = (string) config('auth.admin_pin', '252009');
        $enteredPin = trim((string) $request->input('pin'));

        if ($enteredPin === $expectedPin) {
            session([
                'admin_pin_verified' => true,
                'admin_pin_verified_at' => now(),
            ]);

            $intended = session()->get('url.intended');
            if ($intended && !str_contains($intended, '/admin')) {
                session()->forget('url.intended');
                $intended = null;
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('status', 'Verifikasi PIN berhasil. Selamat datang di Panel Administrator.');
        }

        return back()->withErrors([
            'pin' => 'PIN keamanan yang Anda masukkan tidak sesuai. Silakan coba lagi.',
        ]);
    }
}
