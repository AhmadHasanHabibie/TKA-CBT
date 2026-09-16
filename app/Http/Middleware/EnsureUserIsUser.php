<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsUser
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

        if (Auth::user()->role !== 'user') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Akses khusus Peserta.'], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda berada di akun Administrator.');
        }

        return $next($request);
    }
}
