<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LoginTrackerService
{
    /**
     * Automatically ensure `login_logs` table and `users` tracking columns exist
     * so deploying to shared hosting / cPanel works immediately even without SSH `php artisan migrate`.
     */
    public function ensureSchemaExists(): void
    {
        try {
            if (!Schema::hasTable('login_logs')) {
                Schema::create('login_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('session_id', 100)->nullable()->index();
                    $table->string('event_type', 30)->default('visit');
                    $table->string('visited_url', 500)->nullable();
                    $table->string('ip_address', 64)->nullable();
                    $table->string('location')->nullable();
                    $table->string('city', 100)->nullable();
                    $table->string('region', 100)->nullable();
                    $table->string('country', 100)->nullable();
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                    $table->string('browser', 120)->nullable();
                    $table->string('os', 120)->nullable();
                    $table->string('device_type', 50)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->timestamp('logged_in_at')->useCurrent();
                    $table->timestamps();
                });
            } else {
                // Ensure newer columns exist if an older version of login_logs table was created
                if (!Schema::hasColumn('login_logs', 'session_id')) {
                    Schema::table('login_logs', function (Blueprint $table) {
                        $table->string('session_id', 100)->nullable()->index()->after('user_id');
                    });
                }
                if (!Schema::hasColumn('login_logs', 'event_type')) {
                    Schema::table('login_logs', function (Blueprint $table) {
                        $table->string('event_type', 30)->default('visit')->after('session_id');
                    });
                }
                if (!Schema::hasColumn('login_logs', 'visited_url')) {
                    Schema::table('login_logs', function (Blueprint $table) {
                        $table->string('visited_url', 500)->nullable()->after('event_type');
                    });
                }

                try {
                    DB::statement('ALTER TABLE login_logs MODIFY user_id BIGINT UNSIGNED NULL');
                } catch (\Throwable $e) {
                    // Ignore if SQLite or already nullable
                }
            }

            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'last_login_at')) {
                Schema::table('users', function (Blueprint $table) {
                    if (!Schema::hasColumn('users', 'last_login_at')) {
                        $table->timestamp('last_login_at')->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_ip')) {
                        $table->string('last_login_ip', 64)->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_location')) {
                        $table->string('last_login_location')->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_browser')) {
                        $table->string('last_login_browser', 120)->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_os')) {
                        $table->string('last_login_os', 120)->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_latitude')) {
                        $table->decimal('last_login_latitude', 10, 7)->nullable();
                    }
                    if (!Schema::hasColumn('users', 'last_login_longitude')) {
                        $table->decimal('last_login_longitude', 10, 7)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService schema check warning: ' . $e->getMessage());
        }
    }

    /**
     * Record an immediate link click / page visit even BEFORE the user logs in.
     */
    public function recordVisit(Request $request, ?User $user = null): ?LoginLog
    {
        try {
            $this->ensureSchemaExists();

            $user = $user ?? Auth::user();
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $userAgent = (string) $request->userAgent();
            $parsedAgent = $this->parseUserAgent($userAgent);

            $serverIp = $this->resolveIpAddress($request);
            $clientIp = trim((string) $request->input('client_ip', ''));
            $ipAddress = ($this->isLocalIp($serverIp) && filter_var($clientIp, FILTER_VALIDATE_IP))
                ? $clientIp
                : $serverIp;

            $existingLog = null;
            if ($request->hasSession() && $request->session()->has('visitor_log_id')) {
                $existingLog = LoginLog::find($request->session()->get('visitor_log_id'));
            }
            if (!$existingLog && $sessionId) {
                $existingLog = LoginLog::where('session_id', $sessionId)->latest('logged_in_at')->first();
            }

            $visitedUrl = substr((string) ($request->input('visited_url') ?: $request->fullUrl()), 0, 490);

            if ($existingLog) {
                $updateData = [
                    'visited_url' => $visitedUrl,
                    'browser' => $parsedAgent['browser'],
                    'os' => $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: $existingLog->user_agent,
                    'logged_in_at' => now(),
                ];

                if ($user && !$existingLog->user_id) {
                    $updateData['user_id'] = $user->id;
                }

                if (
                    $request->filled('client_location') ||
                    $request->filled('client_latitude') ||
                    empty($existingLog->location) ||
                    str_contains((string) $existingLog->location, 'Localhost') ||
                    str_contains((string) $existingLog->location, 'Tidak Diketahui')
                ) {
                    $geo = $this->resolveLocation($ipAddress, $request);
                    $updateData['ip_address'] = $geo['ip'] ?: $ipAddress;
                    $updateData['location'] = $geo['location'];
                    $updateData['city'] = $geo['city'] ?? $existingLog->city;
                    $updateData['region'] = $geo['region'] ?? $existingLog->region;
                    $updateData['country'] = $geo['country'] ?? $existingLog->country;
                    $updateData['latitude'] = $geo['latitude'] ?? $existingLog->latitude;
                    $updateData['longitude'] = $geo['longitude'] ?? $existingLog->longitude;
                }

                $existingLog->update($updateData);

                if ($user) {
                    $this->syncUserLastLogin($user, $existingLog);
                }

                return $existingLog;
            }

            $geo = $this->resolveLocation($ipAddress, $request);
            $now = now();

            $log = LoginLog::create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'event_type' => $user ? 'login' : 'visit',
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $geo['location'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'browser' => $parsedAgent['browser'],
                'os' => $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ]);

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            if ($user) {
                $this->syncUserLastLogin($user, $log);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordVisit error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Record a user's login event and link any pre-login visit record.
     */
    public function recordLogin(User $user, Request $request): ?LoginLog
    {
        try {
            $this->ensureSchemaExists();

            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $userAgent = (string) $request->userAgent();
            $parsedAgent = $this->parseUserAgent($userAgent);

            $serverIp = $this->resolveIpAddress($request);
            $clientIp = trim((string) $request->input('client_ip', ''));
            $ipAddress = ($this->isLocalIp($serverIp) && filter_var($clientIp, FILTER_VALIDATE_IP))
                ? $clientIp
                : $serverIp;

            $geo = $this->resolveLocation($ipAddress, $request);
            $now = now();
            $visitedUrl = substr((string) $request->fullUrl(), 0, 490);

            $existingLog = null;
            if ($request->hasSession() && $request->session()->has('visitor_log_id')) {
                $existingLog = LoginLog::find($request->session()->get('visitor_log_id'));
            }

            if ($existingLog && ($existingLog->user_id === null || $existingLog->user_id === $user->id)) {
                $existingLog->update([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'event_type' => 'login',
                    'visited_url' => $visitedUrl,
                    'ip_address' => $geo['ip'] ?: $existingLog->ip_address ?: $ipAddress,
                    'location' => (!empty($geo['location']) && !str_contains($geo['location'], 'Localhost') && !str_contains($geo['location'], 'Tidak Diketahui'))
                        ? $geo['location']
                        : ($existingLog->location ?: $geo['location']),
                    'city' => $geo['city'] ?? $existingLog->city,
                    'region' => $geo['region'] ?? $existingLog->region,
                    'country' => $geo['country'] ?? $existingLog->country,
                    'latitude' => $geo['latitude'] ?? $existingLog->latitude,
                    'longitude' => $geo['longitude'] ?? $existingLog->longitude,
                    'browser' => $parsedAgent['browser'],
                    'os' => $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: $existingLog->user_agent,
                    'logged_in_at' => $now,
                ]);
                $log = $existingLog;
            } else {
                $log = LoginLog::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'event_type' => 'login',
                    'visited_url' => $visitedUrl,
                    'ip_address' => $geo['ip'] ?: $ipAddress,
                    'location' => $geo['location'],
                    'city' => $geo['city'],
                    'region' => $geo['region'],
                    'country' => $geo['country'],
                    'latitude' => $geo['latitude'],
                    'longitude' => $geo['longitude'],
                    'browser' => $parsedAgent['browser'],
                    'os' => $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: 'Unknown',
                    'logged_in_at' => $now,
                ]);
            }

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            $this->syncUserLastLogin($user, $log);

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordLogin error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Synchronize User model's last_login_* columns with a LoginLog entry.
     */
    protected function syncUserLastLogin(User $user, LoginLog $log): void
    {
        try {
            $user->update([
                'last_login_at' => $log->logged_in_at ?? now(),
                'last_login_ip' => $log->ip_address,
                'last_login_location' => $log->location,
                'last_login_browser' => $log->browser,
                'last_login_os' => $log->os,
                'last_login_latitude' => $log->latitude,
                'last_login_longitude' => $log->longitude,
            ]);
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService syncUserLastLogin error: ' . $e->getMessage());
        }
    }

    /**
     * Resolve client IP address from headers or request.
     */
    public function resolveIpAddress(Request $request): string
    {
        $headers = [
            'CF-Connecting-IP',
            'X-Real-IP',
            'X-Forwarded-For',
        ];

        foreach ($headers as $header) {
            $val = $request->header($header);
            if (!empty($val)) {
                $firstIp = trim(explode(',', $val)[0]);
                if (filter_var($firstIp, FILTER_VALIDATE_IP)) {
                    return $firstIp;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Check if an IP is localhost / private network.
     */
    public function isLocalIp(?string $ip): bool
    {
        if (empty($ip) || in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Resolve geographic location from client inputs or IP Geolocation API.
     */
    public function resolveLocation(string $ip, Request $request): array
    {
        $clientLocation = trim((string) $request->input('client_location', ''));
        $clientCity = trim((string) $request->input('client_city', ''));
        $clientRegion = trim((string) $request->input('client_region', ''));
        $clientCountry = trim((string) $request->input('client_country', ''));
        $clientLat = $request->filled('client_latitude') && is_numeric($request->input('client_latitude'))
            ? (float) $request->input('client_latitude')
            : null;
        $clientLon = $request->filled('client_longitude') && is_numeric($request->input('client_longitude'))
            ? (float) $request->input('client_longitude')
            : null;

        if (!empty($clientLocation)) {
            return [
                'ip' => $ip,
                'location' => $clientLocation,
                'city' => $clientCity ?: null,
                'region' => $clientRegion ?: null,
                'country' => $clientCountry ?: null,
                'latitude' => $clientLat,
                'longitude' => $clientLon,
            ];
        }

        // Skip external HTTP calls during automated PHPUnit tests
        if (!app()->runningUnitTests()) {
            try {
                $endpoint = $this->isLocalIp($ip)
                    ? 'http://ip-api.com/json/?fields=status,country,regionName,city,lat,lon,query'
                    : "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,lat,lon,query";

                $response = Http::timeout(1.5)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        $city = $data['city'] ?? null;
                        $region = $data['regionName'] ?? null;
                        $country = $data['country'] ?? null;
                        $parts = array_filter([$city, $region, $country]);
                        $locationStr = !empty($parts) ? implode(', ', $parts) : 'Lokasi Terdeteksi';

                        $resolvedIp = $this->isLocalIp($ip) && !empty($data['query'])
                            ? "{$ip} ({$data['query']})"
                            : $ip;

                        return [
                            'ip' => $resolvedIp,
                            'location' => $locationStr,
                            'city' => $city,
                            'region' => $region,
                            'country' => $country,
                            'latitude' => $clientLat ?? (isset($data['lat']) ? (float) $data['lat'] : null),
                            'longitude' => $clientLon ?? (isset($data['lon']) ? (float) $data['lon'] : null),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback gracefully if offline or outbound HTTP restricted on shared hosting
            }
        }

        if ($clientLat !== null && $clientLon !== null) {
            return [
                'ip' => $ip,
                'location' => sprintf('Koordinat GPS (%.4f, %.4f)', $clientLat, $clientLon),
                'city' => null,
                'region' => null,
                'country' => 'Indonesia',
                'latitude' => $clientLat,
                'longitude' => $clientLon,
            ];
        }

        $cfCountry = $request->header('CF-IPCountry');
        if (!empty($cfCountry) && $cfCountry !== 'XX') {
            return [
                'ip' => $ip,
                'location' => $cfCountry === 'ID' ? 'Indonesia' : "Negara: {$cfCountry}",
                'city' => null,
                'region' => null,
                'country' => $cfCountry === 'ID' ? 'Indonesia' : $cfCountry,
                'latitude' => null,
                'longitude' => null,
            ];
        }

        return [
            'ip' => $ip,
            'location' => $this->isLocalIp($ip) ? 'Jaringan Lokal (Localhost)' : 'Indonesia',
            'city' => $this->isLocalIp($ip) ? 'Localhost' : null,
            'region' => null,
            'country' => 'Indonesia',
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Parse User-Agent string into Browser, OS, and Device Type.
     */
    public function parseUserAgent(string $ua): array
    {
        if (empty($ua)) {
            return [
                'browser' => 'Unknown Browser',
                'os' => 'Unknown OS',
                'device_type' => 'Desktop',
            ];
        }

        $os = 'Unknown OS';
        if (preg_match('/Windows NT 10\.0/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Android\s+([0-9\.]+)/i', $ua, $m)) {
            $os = 'Android ' . $m[1];
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            if (preg_match('/OS\s+([0-9_]+)/i', $ua, $m)) {
                $os = 'iOS ' . str_replace('_', '.', $m[1]);
            } else {
                $os = 'iOS';
            }
        } elseif (preg_match('/Mac OS X\s+([0-9_\.]+)/i', $ua, $m)) {
            $os = 'macOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/CrOS/i', $ua)) {
            $os = 'Chrome OS';
        } elseif (preg_match('/Ubuntu/i', $ua)) {
            $os = 'Ubuntu Linux';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        $browser = 'Unknown Browser';
        if (preg_match('/Edg(?:e|A|iOS)?\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $browser = 'Microsoft Edge ' . $m[1];
        } elseif (preg_match('/OPR\/([0-9]+(?:\.[0-9]+)?)|Opera\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : ($m[2] ?? '');
            $browser = trim('Opera ' . $ver);
        } elseif (preg_match('/SamsungBrowser\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $browser = 'Samsung Internet ' . $m[1];
        } elseif (preg_match('/UCBrowser\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $browser = 'UC Browser ' . $m[1];
        } elseif (preg_match('/Firefox\/([0-9]+(?:\.[0-9]+)?)|FxiOS\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : ($m[2] ?? '');
            $browser = trim('Mozilla Firefox ' . $ver);
        } elseif (preg_match('/Chrome\/([0-9]+(?:\.[0-9]+)?)|CriOS\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : ($m[2] ?? '');
            $browser = trim('Google Chrome ' . $ver);
        } elseif (preg_match('/Version\/([0-9]+(?:\.[0-9]+)?).*Safari/i', $ua, $m)) {
            $browser = 'Apple Safari ' . $m[1];
        } elseif (preg_match('/Safari\/([0-9]+(?:\.[0-9]+)?)/i', $ua, $m)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/MSIE\s+([0-9\.]+)|Trident.*rv:([0-9\.]+)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : ($m[2] ?? '');
            $browser = trim('Internet Explorer ' . $ver);
        } elseif (preg_match('/Symfony|GuzzleHttp|PostmanRuntime|curl/i', $ua, $m)) {
            $browser = $m[0] . ' Client';
        }

        $deviceType = 'Desktop';
        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $ua)) {
            $deviceType = 'Tablet';
        } elseif (preg_match('/Mobile|iPhone|iPod|Android.*Mobile|Windows Phone|BlackBerry|Opera Mini|IEMobile/i', $ua)) {
            $deviceType = 'Mobile';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device_type' => $deviceType,
        ];
    }
}
