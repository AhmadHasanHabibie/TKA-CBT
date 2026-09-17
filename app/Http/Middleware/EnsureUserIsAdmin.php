<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Akses khusus Administrator.'], 403);
            }
            return redirect()->route('user.dashboard')->with('error', 'Akses ditolak. Halaman ini khusus Administrator.');
        }

        // Verify administrator security PIN session
        if (!session('admin_pin_verified')) {
            if ($request->routeIs('admin.pin.*') || $request->is('admin/verify-pin*')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'PIN Keamanan Administrator belum diverifikasi.'], 403);
            }

            return redirect()->route('login');
        }


        return $next($request);

    }
}
