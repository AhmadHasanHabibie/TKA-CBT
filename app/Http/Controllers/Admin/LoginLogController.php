<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    /**
     * Display a listing of visitor link clicks & user login logs (Location, IP Address, Browser, OS).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $logs = LoginLog::with('user')
            ->when($role === 'guest', function ($query) {
                $query->whereNull('user_id');
            })
            ->when($role && in_array($role, ['user', 'admin'], true), function ($query) use ($role) {
                $query->whereHas('user', function ($uq) use ($role) {
                    $uq->where('role', $role);
                });
            })
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('ip_address', 'like', "%{$term}%")
                      ->orWhere('location', 'like', "%{$term}%")
                      ->orWhere('browser', 'like', "%{$term}%")
                      ->orWhere('os', 'like', "%{$term}%")
                      ->orWhere('visited_url', 'like', "%{$term}%")
                      ->orWhereHas('user', function ($uq) use ($term) {
                          $uq->where('name', 'like', "%{$term}%")
                             ->orWhere('email', 'like', "%{$term}%");
                      });
                });
            })
            ->latest('logged_in_at')
            ->paginate(15)
            ->withQueryString();

        $totalLogs = LoginLog::count();
        $guestClicks = LoginLog::whereNull('user_id')->count();
        $uniqueIps = LoginLog::distinct('ip_address')->count('ip_address');
        $topBrowser = LoginLog::selectRaw('browser, count(*) as total')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->value('browser') ?? '—';

        // Detect shared IPs across multiple distinct users (anti-cheating indicator)
        $sharedIps = LoginLog::whereNotNull('ip_address')
            ->selectRaw('ip_address, count(*) as hits')
            ->groupBy('ip_address')
            ->havingRaw('count(*) > 1')
            ->pluck('hits', 'ip_address')
            ->all();

        return view('admin.login-logs.index', compact(
            'logs',
            'search',
            'role',
            'totalLogs',
            'guestClicks',
            'uniqueIps',
            'topBrowser',
            'sharedIps'
        ));
    }

    /**
     * Delete a single login log record.
     */
    public function destroy(LoginLog $loginLog)
    {
        $loginLog->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Data riwayat akses berhasil dihapus.');
    }

    /**
     * Clear all login logs.
     */
    public function clear()
    {
        LoginLog::query()->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Seluruh riwayat log akses & lokasi berhasil dibersihkan.');
    }
}
