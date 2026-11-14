<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Services\LoginTrackerService;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    /**
     * Display a listing of visitor link clicks & user login logs (Location, IP Address, Browser, OS).
     */
    public function index(Request $request, LoginTrackerService $loginTracker)
    {
        $loginTracker->ensureSchemaExists();

        $search = $request->query('search');
        $role = $request->query('role');

        $logs = LoginLog::with('user')
            ->when($role === 'guest', function ($query) {
                $query->whereNull('user_id')->where('event_type', 'visit');
            })
            ->when($role === 'search', function ($query) {
                $query->whereIn('event_type', ['search_documentation', 'search_empty', 'search_found', 'search_not_found']);
            })
            ->when($role === 'failed', function ($query) {
                $query->where('event_type', 'failed_login');
            })
            ->when($role && in_array($role, ['user', 'admin'], true), function ($query) use ($role) {
                $query->whereHas('user', function ($uq) use ($role) {
                    $uq->where('role', $role);
                })->where('event_type', '!=', 'failed_login');
            })
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('ip_address', 'like', "%{$term}%")
                      ->orWhere('location', 'like', "%{$term}%")
                      ->orWhere('browser', 'like', "%{$term}%")
                      ->orWhere('os', 'like', "%{$term}%")
                      ->orWhere('visited_url', 'like', "%{$term}%")
                      ->orWhere('attempted_email', 'like', "%{$term}%")
                      ->orWhere('school_name', 'like', "%{$term}%")
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
        $guestClicks = LoginLog::whereNull('user_id')->where('event_type', '!=', 'failed_login')->count();
        $failedLogins = LoginLog::where('event_type', 'failed_login')->count();
        $uniqueIps = LoginLog::distinct('ip_address')->count('ip_address');
        $topBrowser = LoginLog::selectRaw('browser, COUNT(*) as total')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByRaw('COUNT(*) DESC')
            ->value('browser') ?? '—';

        // Detect shared IPs across multiple accesses (anti-cheating indicator)
        $sharedIps = LoginLog::whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) as hits')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(ip_address) > 1')
            ->pluck('hits', 'ip_address')
            ->all();

        return view('admin.login-logs.index', compact(
            'logs',
            'search',
            'role',
            'totalLogs',
            'guestClicks',
            'failedLogins',
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
    public function clear(LoginTrackerService $loginTracker)
    {
        $loginTracker->ensureSchemaExists();
        LoginLog::query()->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Seluruh riwayat log akses & lokasi berhasil dibersihkan.');
    }
}
