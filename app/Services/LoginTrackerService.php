<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginTrackerService
{
    /**
     * Record an immediate link click / page visit even BEFORE the user logs in.
     */
    public function recordVisit(Request $request, ?User $user = null): LoginLog
    {
        $user = $user ?? Auth::user();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;
        $userAgent = (string) $request->userAgent();
        $parsedAgent = $this->parseUserAgent($userAgent);

        $serverIp = $this->resolveIpAddress($request);
        $clientIp = trim((string) $request->input('client_ip', ''));
        $ipAddress = ($this->isLocalIp($serverIp) && filter_var($clientIp, FILTER_VALIDATE_IP))
            ? $clientIp
            : $serverIp;

        // Check if this visitor session already has a log entry so we don't spam duplicate rows on every refresh
        $existingLog = null;
        if ($request->hasSession() && $request->session()->has('visitor_log_id')) {
            $existingLog = LoginLog::find($request->session()->get('visitor_log_id'));
        }
        if (!$existingLog && $sessionId) {
            $existingLog = LoginLog::where('session_id', $sessionId)->latest('logged_in_at')->first();
        }

        $visitedUrl = $request->input('visited_url') ?: $request->fullUrl();

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

            // Only re-lookup geo if location wasn't resolved yet or client sent fresh geo data
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
    }

    /**
     * Record a user's login event and link any pre-login visit record.
     */
    public function recordLogin(User $user, Request $request): LoginLog
    {
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

        // Check if there was a pre-login 'visit' log in this session
        $existingLog = null;
        if ($request->hasSession() && $request->session()->has('visitor_log_id')) {
            $existingLog = LoginLog::find($request->session()->get('visitor_log_id'));
        }

        if ($existingLog && ($existingLog->user_id === null || $existingLog->user_id === $user->id)) {
            $existingLog->update([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'event_type' => 'login',
                'visited_url' => $request->fullUrl(),
                'ip_address' => $geo['ip'] ?: $existingLog->ip_address ?: $ipAddress,
                'location' => (!empty($geo['location']) && !str_contains($geo['location'], 'Localhost'))
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
                'visited_url' => $request->fullUrl(),
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
    }

    /**
     * Synchronize User model's last_login_* columns with a LoginLog entry.
     */
    protected function syncUserLastLogin(User $user, LoginLog $log): void
    {
        $user->update([
            'last_login_at' => $log->logged_in_at ?? now(),
            'last_login_ip' => $log->ip_address,
            'last_login_location' => $log->location,
            'last_login_browser' => $log->browser,
            'last_login_os' => $log->os,
            'last_login_latitude' => $log->latitude,
            'last_login_longitude' => $log->longitude,
        ]);
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
                // If IP is local (e.g. Laragon 127.0.0.1), querying ip-api without IP resolves the machine's public ISP IP & location
                $endpoint = $this->isLocalIp($ip)
                    ? 'http://ip-api.com/json/?fields=status,country,regionName,city,lat,lon,query'
                    : "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,lat,lon,query";

                $response = Http::timeout(2)->get($endpoint);

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
                // Fallback gracefully if offline
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

        return [
            'ip' => $ip,
            'location' => $this->isLocalIp($ip) ? 'Jaringan Lokal (Localhost)' : 'Tidak Diketahui',
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

        // 1. Detect OS / Platform
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

        // 2. Detect Browser & Version
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

        // 3. Detect Device Type
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
