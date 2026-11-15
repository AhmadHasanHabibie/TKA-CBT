<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Services\LoginTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LoginLogController extends Controller
{
    public function index(Request $request, LoginTrackerService $loginTracker)
    {
        $loginTracker->ensureSchemaExists();

        $search = $request->query('search');
        $role = $request->query('role');

        $existingColumns = Schema::hasTable('login_logs')
            ? Schema::getColumnListing('login_logs')
            : [];

        $hasVpnCol = in_array('is_vpn_suspected', $existingColumns, true);
        $hasFpCol = in_array('device_fingerprint', $existingColumns, true);

        $logs = LoginLog::with('user')
            ->when($role === 'guest', function ($query) {
                $query->whereNull('user_id')->where('event_type', 'visit');
            })
            ->when($role === 'search', function ($query) {
                $query->whereIn('event_type', ['search_documentation', 'search_empty', 'search_found', 'search_not_found', 'search_school']);
            })
            ->when($role === 'email', function ($query) {
                $query->where('event_type', 'email_input');
            })
            ->when($role === 'otp', function ($query) {
                $query->where('event_type', 'otp_attempt');
            })
            ->when($role === 'location', function ($query) {
                $query->where('event_type', 'location_check');
            })
            ->when($role === 'failed', function ($query) {
                $query->where('event_type', 'failed_login');
            })
            ->when($role === 'vpn' && $hasVpnCol, function ($query) {
                $query->where(function ($q) {
                    $q->where('is_vpn_suspected', true)
                      ->orWhere('is_proxy', true)
                      ->orWhere('is_hosting', true);
                });
            })
            ->when($role && in_array($role, ['user', 'admin'], true), function ($query) use ($role) {
                $query->whereHas('user', function ($uq) use ($role) {
                    $uq->where('role', $role);
                })->where('event_type', '!=', 'failed_login');
            })
            ->when($search, function ($query, $term) use ($existingColumns) {
                $query->where(function ($q) use ($term, $existingColumns) {
                    $q->where('ip_address', 'like', "%{$term}%")
                      ->orWhere('location', 'like', "%{$term}%")
                      ->orWhere('browser', 'like', "%{$term}%")
                      ->orWhere('os', 'like', "%{$term}%")
                      ->orWhere('visited_url', 'like', "%{$term}%")
                      ->orWhere('attempted_email', 'like', "%{$term}%")
                      ->orWhere('school_name', 'like', "%{$term}%");

                    foreach (['district', 'isp', 'org', 'hostname', 'device_model', 'device_fingerprint', 'gpu_renderer'] as $extraCol) {
                        if (in_array($extraCol, $existingColumns, true)) {
                            $q->orWhere($extraCol, 'like', "%{$term}%");
                        }
                    }

                    $q->orWhereHas('user', function ($uq) use ($term) {
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

        $sharedIps = LoginLog::whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) as hits')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(ip_address) > 1')
            ->pluck('hits', 'ip_address')
            ->all();

        $sharedIpUsers = [];
        try {
            $sharedIpUsers = LoginLog::whereNotNull('ip_address')
                ->whereNotNull('user_id')
                ->selectRaw('ip_address, COUNT(DISTINCT user_id) as user_count')
                ->groupBy('ip_address')
                ->havingRaw('COUNT(DISTINCT user_id) > 1')
                ->pluck('user_count', 'ip_address')
                ->all();
        } catch (\Throwable $e) {
        }

        $fingerprintOwners = [];
        if ($hasFpCol) {
            try {
                $knownLogs = LoginLog::with('user')
                    ->whereNotNull('user_id')
                    ->whereNotNull('device_fingerprint')
                    ->where('device_fingerprint', '!=', '')
                    ->latest('logged_in_at')
                    ->get();

                foreach ($knownLogs as $kLog) {
                    if (!$kLog->user || !$kLog->device_fingerprint) {
                        continue;
                    }
                    $fp = $kLog->device_fingerprint;
                    if (!isset($fingerprintOwners[$fp]) || ($fingerprintOwners[$fp]['role'] === 'admin' && $kLog->user->role === 'user')) {
                        $fingerprintOwners[$fp] = [
                            'id' => $kLog->user->id,
                            'name' => $kLog->user->name,
                            'email' => $kLog->user->email,
                            'role' => $kLog->user->role,
                        ];
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return view('admin.login-logs.index', compact(
            'logs',
            'search',
            'role',
            'totalLogs',
            'guestClicks',
            'failedLogins',
            'uniqueIps',
            'topBrowser',
            'sharedIps',
            'sharedIpUsers',
            'fingerprintOwners'
        ));
    }

    public function destroy(LoginLog $loginLog)
    {
        $loginLog->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Data riwayat akses berhasil dihapus.');
    }

    public function clear(LoginTrackerService $loginTracker)
    {
        $loginTracker->ensureSchemaExists();
        LoginLog::query()->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Seluruh riwayat log akses & lokasi berhasil dibersihkan.');
    }
}
