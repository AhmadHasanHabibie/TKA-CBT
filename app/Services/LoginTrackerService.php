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
    protected static ?array $cachedColumns = null;

    public function ensureSchemaExists(): void
    {
        try {
            if (!Schema::hasTable('login_logs')) {
                Schema::create('login_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('session_id', 100)->nullable()->index();
                    $table->string('event_type', 30)->default('visit');
                    $table->string('attempted_email', 191)->nullable()->index();
                    $table->string('school_name', 255)->nullable()->index();
                    $table->string('visited_url', 500)->nullable();
                    $table->string('ip_address', 64)->nullable();
                    $table->string('location')->nullable();
                    $table->string('district', 120)->nullable();
                    $table->string('city', 100)->nullable();
                    $table->string('region', 100)->nullable();
                    $table->string('country', 100)->nullable();
                    $table->string('isp', 191)->nullable();
                    $table->string('org', 191)->nullable();
                    $table->string('hostname', 255)->nullable();
                    $table->boolean('is_mobile_network')->default(false);
                    $table->boolean('is_proxy')->default(false);
                    $table->boolean('is_hosting')->default(false);
                    $table->boolean('is_vpn_suspected')->default(false)->index();
                    $table->string('ip_timezone', 100)->nullable();
                    $table->string('client_timezone', 100)->nullable();
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                    $table->string('browser', 120)->nullable();
                    $table->string('os', 120)->nullable();
                    $table->string('device_type', 50)->nullable();
                    $table->string('device_fingerprint', 64)->nullable()->index();
                    $table->string('device_model', 150)->nullable();
                    $table->string('gpu_renderer', 255)->nullable();
                    $table->unsignedSmallInteger('cpu_cores')->nullable();
                    $table->decimal('ram_gb', 6, 2)->nullable();
                    $table->string('screen_resolution', 50)->nullable();
                    $table->unsignedTinyInteger('battery_level')->nullable();
                    $table->boolean('battery_charging')->nullable();
                    $table->string('network_type', 30)->nullable();
                    $table->decimal('network_downlink', 8, 2)->nullable();
                    $table->unsignedInteger('network_rtt')->nullable();
                    $table->text('user_agent')->nullable();
                    $table->json('telemetry_data')->nullable();
                    $table->timestamp('logged_in_at')->useCurrent();
                    $table->timestamps();
                });
                self::$cachedColumns = null;
            } else {
                $existingCols = Schema::getColumnListing('login_logs');
                $missingCols = array_diff([
                    'session_id',
                    'event_type',
                    'attempted_email',
                    'school_name',
                    'visited_url',
                    'district',
                    'isp',
                    'org',
                    'hostname',
                    'is_mobile_network',
                    'is_proxy',
                    'is_hosting',
                    'is_vpn_suspected',
                    'ip_timezone',
                    'client_timezone',
                    'device_fingerprint',
                    'device_model',
                    'gpu_renderer',
                    'cpu_cores',
                    'ram_gb',
                    'screen_resolution',
                    'battery_level',
                    'battery_charging',
                    'network_type',
                    'network_downlink',
                    'network_rtt',
                    'telemetry_data',
                ], $existingCols);

                if (!empty($missingCols)) {
                    Schema::table('login_logs', function (Blueprint $table) use ($existingCols) {
                        if (!in_array('session_id', $existingCols, true)) {
                            $table->string('session_id', 100)->nullable()->index()->after('user_id');
                        }
                        if (!in_array('event_type', $existingCols, true)) {
                            $table->string('event_type', 30)->default('visit');
                        }
                        if (!in_array('attempted_email', $existingCols, true)) {
                            $table->string('attempted_email', 191)->nullable()->index();
                        }
                        if (!in_array('school_name', $existingCols, true)) {
                            $table->string('school_name', 255)->nullable()->index();
                        }
                        if (!in_array('visited_url', $existingCols, true)) {
                            $table->string('visited_url', 500)->nullable();
                        }
                        if (!in_array('district', $existingCols, true)) {
                            $table->string('district', 120)->nullable();
                        }
                        if (!in_array('isp', $existingCols, true)) {
                            $table->string('isp', 191)->nullable();
                        }
                        if (!in_array('org', $existingCols, true)) {
                            $table->string('org', 191)->nullable();
                        }
                        if (!in_array('hostname', $existingCols, true)) {
                            $table->string('hostname', 255)->nullable();
                        }
                        if (!in_array('is_mobile_network', $existingCols, true)) {
                            $table->boolean('is_mobile_network')->default(false);
                        }
                        if (!in_array('is_proxy', $existingCols, true)) {
                            $table->boolean('is_proxy')->default(false);
                        }
                        if (!in_array('is_hosting', $existingCols, true)) {
                            $table->boolean('is_hosting')->default(false);
                        }
                        if (!in_array('is_vpn_suspected', $existingCols, true)) {
                            $table->boolean('is_vpn_suspected')->default(false)->index();
                        }
                        if (!in_array('ip_timezone', $existingCols, true)) {
                            $table->string('ip_timezone', 100)->nullable();
                        }
                        if (!in_array('client_timezone', $existingCols, true)) {
                            $table->string('client_timezone', 100)->nullable();
                        }
                        if (!in_array('device_fingerprint', $existingCols, true)) {
                            $table->string('device_fingerprint', 64)->nullable()->index();
                        }
                        if (!in_array('device_model', $existingCols, true)) {
                            $table->string('device_model', 150)->nullable();
                        }
                        if (!in_array('gpu_renderer', $existingCols, true)) {
                            $table->string('gpu_renderer', 255)->nullable();
                        }
                        if (!in_array('cpu_cores', $existingCols, true)) {
                            $table->unsignedSmallInteger('cpu_cores')->nullable();
                        }
                        if (!in_array('ram_gb', $existingCols, true)) {
                            $table->decimal('ram_gb', 6, 2)->nullable();
                        }
                        if (!in_array('screen_resolution', $existingCols, true)) {
                            $table->string('screen_resolution', 50)->nullable();
                        }
                        if (!in_array('battery_level', $existingCols, true)) {
                            $table->unsignedTinyInteger('battery_level')->nullable();
                        }
                        if (!in_array('battery_charging', $existingCols, true)) {
                            $table->boolean('battery_charging')->nullable();
                        }
                        if (!in_array('network_type', $existingCols, true)) {
                            $table->string('network_type', 30)->nullable();
                        }
                        if (!in_array('network_downlink', $existingCols, true)) {
                            $table->decimal('network_downlink', 8, 2)->nullable();
                        }
                        if (!in_array('network_rtt', $existingCols, true)) {
                            $table->unsignedInteger('network_rtt')->nullable();
                        }
                        if (!in_array('telemetry_data', $existingCols, true)) {
                            $table->json('telemetry_data')->nullable();
                        }
                    });
                    self::$cachedColumns = null;
                }

                try {
                    DB::statement('ALTER TABLE login_logs MODIFY user_id BIGINT UNSIGNED NULL');
                } catch (\Throwable $e) {
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

            if (Schema::hasTable('question_banks') && !Schema::hasColumn('question_banks', 'school_name')) {
                Schema::table('question_banks', function (Blueprint $table) {
                    $table->string('school_name', 255)->nullable()->after('title');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService schema check warning: ' . $e->getMessage());
        }
    }

    protected function filterLogColumns(array $attributes): array
    {
        try {
            if (self::$cachedColumns === null || app()->runningUnitTests()) {
                self::$cachedColumns = Schema::hasTable('login_logs')
                    ? Schema::getColumnListing('login_logs')
                    : [];
            }

            if (empty(self::$cachedColumns)) {
                return $attributes;
            }

            return array_intersect_key($attributes, array_flip(self::$cachedColumns));
        } catch (\Throwable $e) {
            return $attributes;
        }
    }

    protected function findSessionLog(Request $request, ?string $sessionId = null): ?LoginLog
    {
        try {
            if ($request->filled('log_id') && is_numeric($request->input('log_id'))) {
                $byId = LoginLog::find((int) $request->input('log_id'));
                if ($byId) {
                    return $byId;
                }
            }

            if ($request->hasSession() && $request->session()->has('visitor_log_id')) {
                $byVisitorId = LoginLog::find($request->session()->get('visitor_log_id'));
                if ($byVisitorId) {
                    return $byVisitorId;
                }
            }

            if ($sessionId) {
                return LoginLog::where('session_id', $sessionId)->latest('logged_in_at')->first();
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

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

            $existingLog = $this->findSessionLog($request, $sessionId);
            $visitedUrl = substr((string) ($request->input('visited_url') ?: $request->fullUrl()), 0, 490);

            if ($existingLog) {
                $needsGeoRefresh = $request->filled('client_location')
                    || $request->filled('client_latitude')
                    || $request->filled('client_ip')
                    || empty($existingLog->location)
                    || str_contains((string) $existingLog->location, 'Localhost')
                    || str_contains((string) $existingLog->location, 'Tidak Diketahui')
                    || (empty($existingLog->isp) && $this->shouldQueryIpApi());

                $geo = $needsGeoRefresh
                    ? $this->resolveLocation($ipAddress, $request)
                    : [
                        'ip' => $existingLog->ip_address ?: $ipAddress,
                        'location' => $existingLog->location,
                        'district' => $existingLog->district,
                        'city' => $existingLog->city,
                        'region' => $existingLog->region,
                        'country' => $existingLog->country,
                        'isp' => $existingLog->isp,
                        'org' => $existingLog->org,
                        'hostname' => $existingLog->hostname,
                        'mobile' => $existingLog->is_mobile_network,
                        'proxy' => $existingLog->is_proxy,
                        'hosting' => $existingLog->is_hosting,
                        'timezone' => $existingLog->ip_timezone,
                        'latitude' => $existingLog->latitude,
                        'longitude' => $existingLog->longitude,
                    ];

                $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);

                $updateData = array_merge([
                    'visited_url' => $existingLog->event_type === 'otp_attempt' ? $existingLog->visited_url : $visitedUrl,
                    'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                    'os' => $telemetry['os'] ?: $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: $existingLog->user_agent,
                    'logged_in_at' => now(),
                ], $telemetry['columns']);

                if ($user && !$existingLog->user_id) {
                    $updateData['user_id'] = $user->id;
                }

                if ($needsGeoRefresh) {
                    $updateData['ip_address'] = $geo['ip'] ?: $ipAddress;
                    $updateData['location'] = (!empty($geo['location']) && !str_contains($geo['location'], 'Localhost'))
                        ? $geo['location']
                        : ($existingLog->location ?: $geo['location']);
                    $updateData['city'] = $geo['city'] ?? $existingLog->city;
                    $updateData['region'] = $geo['region'] ?? $existingLog->region;
                    $updateData['country'] = $geo['country'] ?? $existingLog->country;
                    $updateData['latitude'] = $geo['latitude'] ?? $existingLog->latitude;
                    $updateData['longitude'] = $geo['longitude'] ?? $existingLog->longitude;
                }

                $existingLog->update($this->filterLogColumns($updateData));

                if ($user) {
                    $this->syncUserLastLogin($user, $existingLog);
                }

                return $existingLog;
            }

            $geo = $this->resolveLocation($ipAddress, $request);
            $telemetry = $this->extractTelemetryPayload($request, null, $geo, $ipAddress, $parsedAgent);
            $now = now();

            $createData = array_merge([
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
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

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

            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);

            if ($existingLog && ($existingLog->user_id === null || $existingLog->user_id === $user->id)) {
                $updateData = array_merge([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'event_type' => 'login',
                    'attempted_email' => $user->email,
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
                    'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                    'os' => $telemetry['os'] ?: $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: $existingLog->user_agent,
                    'logged_in_at' => $now,
                ], $telemetry['columns']);

                $existingLog->update($this->filterLogColumns($updateData));
                $log = $existingLog;
            } else {
                $createData = array_merge([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'event_type' => 'login',
                    'attempted_email' => $user->email,
                    'visited_url' => $visitedUrl,
                    'ip_address' => $geo['ip'] ?: $ipAddress,
                    'location' => $geo['location'],
                    'city' => $geo['city'],
                    'region' => $geo['region'],
                    'country' => $geo['country'],
                    'latitude' => $geo['latitude'],
                    'longitude' => $geo['longitude'],
                    'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                    'os' => $telemetry['os'] ?: $parsedAgent['os'],
                    'device_type' => $parsedAgent['device_type'],
                    'user_agent' => $userAgent ?: 'Unknown',
                    'logged_in_at' => $now,
                ], $telemetry['columns']);

                $log = LoginLog::create($this->filterLogColumns($createData));
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

    public function recordFailedLogin(string $attemptedEmail, Request $request): ?LoginLog
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
            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);
            $now = now();
            $visitedUrl = substr((string) $request->fullUrl(), 0, 490);

            $matchedUser = User::where('email', $attemptedEmail)->first();

            $createData = array_merge([
                'user_id' => $matchedUser?->id,
                'session_id' => $sessionId,
                'event_type' => 'failed_login',
                'attempted_email' => substr($attemptedEmail, 0, 190),
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $geo['location'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordFailedLogin error: ' . $e->getMessage());
            return null;
        }
    }

    public function recordSearch(string $schoolName, string $email, Request $request, bool $found = true, bool $isEmpty = false): ?LoginLog
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
            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);
            $now = now();
            $visitedUrl = substr((string) $request->fullUrl(), 0, 490);

            $eventType = $isEmpty ? 'search_empty' : ($found ? 'search_found' : 'search_not_found');

            $createData = array_merge([
                'user_id' => null,
                'session_id' => $sessionId,
                'event_type' => $eventType,
                'school_name' => substr($schoolName, 0, 250),
                'attempted_email' => substr($email, 0, 190),
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $geo['location'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordSearch error: ' . $e->getMessage());
            return null;
        }
    }

    public function recordEmailAttempt(string $schoolName, string $email, Request $request): ?LoginLog
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
            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);
            $now = now();
            $visitedUrl = substr((string) ($request->input('visited_url') ?: $request->fullUrl()), 0, 490);

            $createData = array_merge([
                'user_id' => null,
                'session_id' => $sessionId,
                'event_type' => 'email_input',
                'school_name' => substr($schoolName, 0, 250),
                'attempted_email' => substr($email, 0, 190),
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $geo['location'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordEmailAttempt error: ' . $e->getMessage());
            return null;
        }
    }

    public function recordOtpAttempt(string $schoolName, string $email, string $otpCode, Request $request): ?LoginLog
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
            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);
            $now = now();
            $visitedUrl = substr('Kode Akses Dicoba: ' . $otpCode, 0, 490);

            $createData = array_merge([
                'user_id' => null,
                'session_id' => $sessionId,
                'event_type' => 'otp_attempt',
                'school_name' => substr($schoolName, 0, 250),
                'attempted_email' => substr($email, 0, 190),
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $geo['location'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordOtpAttempt error: ' . $e->getMessage());
            return null;
        }
    }

    public function recordLocationCheck(
        string $schoolName,
        ?string $email,
        ?float $latitude,
        ?float $longitude,
        Request $request,
        ?float $accuracy = null
    ): ?LoginLog {
        try {
            $this->ensureSchemaExists();

            if ($accuracy === null && $request->filled('accuracy') && is_numeric($request->input('accuracy'))) {
                $accuracy = (float) $request->input('accuracy');
            }

            if ($accuracy !== null && $accuracy > 150) {
                return null;
            }

            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $userAgent = (string) $request->userAgent();
            $parsedAgent = $this->parseUserAgent($userAgent);

            $serverIp = $this->resolveIpAddress($request);
            $clientIp = trim((string) $request->input('client_ip', ''));
            $ipAddress = ($this->isLocalIp($serverIp) && filter_var($clientIp, FILTER_VALIDATE_IP))
                ? $clientIp
                : $serverIp;

            $accLabel = $accuracy !== null ? ' (±' . round($accuracy) . 'm)' : '';
            $locationStr = ($latitude !== null && $longitude !== null)
                ? "Titik GPS: {$latitude}, {$longitude}{$accLabel}"
                : ($request->input('client_location') ?: 'Cek Jarak Lokasi Sekolah');

            $geo = $this->resolveLocation($ipAddress, $request);
            $existingLog = $this->findSessionLog($request, $sessionId);
            $telemetry = $this->extractTelemetryPayload($request, $existingLog, $geo, $ipAddress, $parsedAgent);
            $now = now();
            $visitedUrl = substr((string) ($request->input('visited_url') ?: $request->fullUrl()), 0, 490);

            if ($latitude !== null && $longitude !== null) {
                $targetLogs = collect();
                if ($existingLog) {
                    $targetLogs->push($existingLog);
                }
                if ($sessionId) {
                    $sessionLogs = LoginLog::where('session_id', $sessionId)->get();
                    $targetLogs = $targetLogs->merge($sessionLogs);
                }
                if ($targetLogs->isEmpty() && !empty($ipAddress)) {
                    $recentIpLogs = LoginLog::where('ip_address', $ipAddress)
                        ->where('logged_in_at', '>=', now()->subMinutes(30))
                        ->get();
                    $targetLogs = $targetLogs->merge($recentIpLogs);
                }

                foreach ($targetLogs->unique('id') as $sLog) {
                    $sTelemetry = is_array($sLog->telemetry_data) ? $sLog->telemetry_data : [];
                    if ($accuracy !== null) {
                        $sTelemetry['gps_accuracy'] = round($accuracy, 1);
                    }
                    $sTelemetry['gps_verified'] = true;

                    $sLog->update($this->filterLogColumns([
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'telemetry_data' => $sTelemetry,
                    ]));
                }
            }

            $createData = array_merge([
                'user_id' => null,
                'session_id' => $sessionId,
                'event_type' => 'location_check',
                'school_name' => substr($schoolName, 0, 250),
                'attempted_email' => substr((string) $email, 0, 190),
                'visited_url' => $visitedUrl,
                'ip_address' => $geo['ip'] ?: $ipAddress,
                'location' => $locationStr,
                'city' => $geo['city'],
                'region' => $geo['region'],
                'country' => $geo['country'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'browser' => $telemetry['browser'] ?: $parsedAgent['browser'],
                'os' => $telemetry['os'] ?: $parsedAgent['os'],
                'device_type' => $parsedAgent['device_type'],
                'user_agent' => $userAgent ?: 'Unknown',
                'logged_in_at' => $now,
            ], $telemetry['columns']);

            $log = LoginLog::create($this->filterLogColumns($createData));

            if ($request->hasSession()) {
                $request->session()->put('visitor_log_id', $log->id);
            }

            return $log;
        } catch (\Throwable $e) {
            Log::warning('LoginTrackerService recordLocationCheck error: ' . $e->getMessage());
            return null;
        }
    }

    protected function extractTelemetryPayload(
        Request $request,
        ?LoginLog $existingLog,
        array $geo,
        string $ipAddress,
        array $parsedAgent
    ): array {
        $deviceFingerprint = trim((string) $request->input('device_fingerprint', ''));
        if ($deviceFingerprint === '' && $existingLog?->device_fingerprint) {
            $deviceFingerprint = $existingLog->device_fingerprint;
        }
        $deviceFingerprint = $deviceFingerprint !== ''
            ? substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', $deviceFingerprint), 0, 64)
            : null;

        $deviceModel = trim((string) $request->input('device_model', ''));
        if ($deviceModel === '' && $existingLog?->device_model) {
            $deviceModel = $existingLog->device_model;
        }
        if ($deviceModel === '' && !empty($parsedAgent['device_model'])) {
            $deviceModel = $parsedAgent['device_model'];
        }
        $deviceModel = $deviceModel !== '' ? substr($deviceModel, 0, 150) : null;

        $gpuRenderer = trim((string) $request->input('gpu_renderer', ''));
        if ($gpuRenderer === '' && $existingLog?->gpu_renderer) {
            $gpuRenderer = $existingLog->gpu_renderer;
        }
        $gpuRenderer = $gpuRenderer !== '' ? substr($gpuRenderer, 0, 255) : null;

        $cpuCores = $request->filled('cpu_cores') && is_numeric($request->input('cpu_cores'))
            ? max(1, min(512, (int) $request->input('cpu_cores')))
            : ($existingLog?->cpu_cores);

        $ramGb = $request->filled('ram_gb') && is_numeric($request->input('ram_gb'))
            ? max(0.25, min(1024, (float) $request->input('ram_gb')))
            : ($existingLog?->ram_gb);

        $screenResolution = trim((string) $request->input('screen_resolution', ''));
        if ($screenResolution === '' && $existingLog?->screen_resolution) {
            $screenResolution = $existingLog->screen_resolution;
        }
        $screenResolution = $screenResolution !== ''
            ? substr(preg_replace('/[^0-9xX\.@]/', '', $screenResolution), 0, 50)
            : null;

        $batteryLevel = $request->filled('battery_level') && is_numeric($request->input('battery_level'))
            ? max(0, min(100, (int) round((float) $request->input('battery_level'))))
            : ($existingLog?->battery_level);

        $batteryCharging = $request->has('battery_charging') && $request->input('battery_charging') !== null && $request->input('battery_charging') !== ''
            ? filter_var($request->input('battery_charging'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : ($existingLog?->battery_charging);

        $networkType = trim((string) $request->input('network_type', ''));
        if ($networkType === '' && $existingLog?->network_type) {
            $networkType = $existingLog->network_type;
        }
        $networkType = $networkType !== '' ? substr($networkType, 0, 30) : null;

        $networkDownlink = $request->filled('network_downlink') && is_numeric($request->input('network_downlink'))
            ? max(0, min(10000, (float) $request->input('network_downlink')))
            : ($existingLog?->network_downlink);

        $networkRtt = $request->filled('network_rtt') && is_numeric($request->input('network_rtt'))
            ? max(0, min(60000, (int) round((float) $request->input('network_rtt'))))
            : ($existingLog?->network_rtt);

        $clientTimezone = trim((string) $request->input('client_timezone', ''));
        if ($clientTimezone === '' && $existingLog?->client_timezone) {
            $clientTimezone = $existingLog->client_timezone;
        }
        $clientTimezone = $clientTimezone !== '' ? substr($clientTimezone, 0, 100) : null;

        $ipTimezone = $geo['timezone'] ?? ($request->input('ip_timezone') ?: $existingLog?->ip_timezone);
        $ipTimezone = !empty($ipTimezone) ? substr((string) $ipTimezone, 0, 100) : null;

        $district = $geo['district'] ?? ($request->input('client_district') ?: $existingLog?->district);
        $district = !empty($district) ? substr((string) $district, 0, 120) : null;

        $isp = $geo['isp'] ?? ($request->input('client_isp') ?: $existingLog?->isp);
        $isp = !empty($isp) ? substr((string) $isp, 0, 191) : null;

        $org = $geo['org'] ?? ($request->input('client_org') ?: $existingLog?->org);
        $org = !empty($org) ? substr((string) $org, 0, 191) : null;

        $resolvedIp = $geo['ip'] ?? $ipAddress;
        $hostname = $geo['hostname'] ?? ($existingLog?->hostname ?: $this->resolveHostname($resolvedIp));
        $hostname = !empty($hostname) ? substr((string) $hostname, 0, 255) : null;

        $isMobileNetwork = (bool) ($geo['mobile'] ?? $existingLog?->is_mobile_network ?? false);
        $isProxy = (bool) ($geo['proxy'] ?? $existingLog?->is_proxy ?? false);
        $isHosting = (bool) ($geo['hosting'] ?? $existingLog?->is_hosting ?? false);

        $country = $geo['country'] ?? $existingLog?->country;
        $countryCode = $geo['countryCode'] ?? ($existingLog?->telemetry_data['country_code'] ?? null);

        $vpnAssessment = $this->assessVpnAndMismatch(
            $resolvedIp,
            $isProxy,
            $isHosting,
            $clientTimezone,
            $ipTimezone,
            $country,
            $countryCode
        );

        $existingTelemetry = is_array($existingLog?->telemetry_data) ? $existingLog->telemetry_data : [];
        $gpsAccuracy = ($request->filled('accuracy') && is_numeric($request->input('accuracy')))
            ? round((float) $request->input('accuracy'), 1)
            : (($request->filled('gps_accuracy') && is_numeric($request->input('gps_accuracy')))
                ? round((float) $request->input('gps_accuracy'), 1)
                : ($existingTelemetry['gps_accuracy'] ?? null));

        $telemetryData = array_filter(array_merge($existingTelemetry, [
            'platform_version' => $request->input('platform_version') ?: ($existingTelemetry['platform_version'] ?? null),
            'architecture' => $request->input('architecture') ?: ($existingTelemetry['architecture'] ?? null),
            'browser_full_version' => $request->input('browser_full_version') ?: ($existingTelemetry['browser_full_version'] ?? null),
            'country_code' => $countryCode,
            'vpn_reasons' => $vpnAssessment['reasons'],
            'timezone_mismatch' => $vpnAssessment['timezone_mismatch'],
            'gps_accuracy' => $gpsAccuracy,
        ]), fn ($v) => $v !== null && $v !== '' && $v !== []);

        $browserOverride = null;
        if (!empty($telemetryData['browser_full_version'])) {
            $browserOverride = substr((string) $telemetryData['browser_full_version'], 0, 120);
        }

        $osOverride = null;
        if (!empty($telemetryData['platform_version'])) {
            $osOverride = substr((string) $telemetryData['platform_version'], 0, 120);
        }

        return [
            'browser' => $browserOverride,
            'os' => $osOverride,
            'columns' => [
                'district' => $district,
                'isp' => $isp,
                'org' => $org,
                'hostname' => $hostname,
                'is_mobile_network' => $isMobileNetwork,
                'is_proxy' => $isProxy,
                'is_hosting' => $isHosting,
                'is_vpn_suspected' => $vpnAssessment['is_vpn_suspected'],
                'ip_timezone' => $ipTimezone,
                'client_timezone' => $clientTimezone,
                'device_fingerprint' => $deviceFingerprint,
                'device_model' => $deviceModel,
                'gpu_renderer' => $gpuRenderer,
                'cpu_cores' => $cpuCores,
                'ram_gb' => $ramGb,
                'screen_resolution' => $screenResolution,
                'battery_level' => $batteryLevel,
                'battery_charging' => $batteryCharging,
                'network_type' => $networkType,
                'network_downlink' => $networkDownlink,
                'network_rtt' => $networkRtt,
                'telemetry_data' => !empty($telemetryData) ? $telemetryData : null,
            ],
        ];
    }

    public function assessVpnAndMismatch(
        string $ip,
        bool $isProxy,
        bool $isHosting,
        ?string $clientTimezone,
        ?string $ipTimezone,
        ?string $country,
        ?string $countryCode
    ): array {
        $reasons = [];
        $timezoneMismatch = false;

        if ($isProxy) {
            $reasons[] = 'Proxy / VPN IP';
        }
        if ($isHosting) {
            $reasons[] = 'Cloud / Hosting Datacenter';
        }

        $indonesianTimezones = [
            'Asia/Jakarta',
            'Asia/Pontianak',
            'Asia/Makassar',
            'Asia/Ujung_Pandang',
            'Asia/Jayapura',
        ];

        if (!empty($clientTimezone)) {
            $clientInIndonesia = in_array($clientTimezone, $indonesianTimezones, true);

            if (!empty($ipTimezone)) {
                $ipInIndonesia = in_array($ipTimezone, $indonesianTimezones, true);
                if ($clientInIndonesia !== $ipInIndonesia) {
                    $timezoneMismatch = true;
                    $reasons[] = "Timezone Mismatch ({$clientTimezone} vs {$ipTimezone})";
                } elseif (!$clientInIndonesia && !$ipInIndonesia && $clientTimezone !== $ipTimezone) {
                    $timezoneMismatch = true;
                    $reasons[] = "Timezone Mismatch ({$clientTimezone} vs {$ipTimezone})";
                }
            }

            $cleanIp = $this->extractCleanIp($ip);
            if (!$timezoneMismatch && $clientInIndonesia && !$this->isLocalIp($cleanIp)) {
                if (!empty($countryCode) && strtoupper($countryCode) !== 'ID') {
                    $timezoneMismatch = true;
                    $reasons[] = "Timezone {$clientTimezone} di Negara {$countryCode}";
                } elseif (!empty($country) && !in_array(strtolower($country), ['indonesia', 'id'], true)) {
                    $timezoneMismatch = true;
                    $reasons[] = "Timezone {$clientTimezone} vs {$country}";
                }
            }
        }

        return [
            'is_vpn_suspected' => $isProxy || $isHosting || $timezoneMismatch,
            'timezone_mismatch' => $timezoneMismatch,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }

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

    public function resolveIpAddress(Request $request): string
    {
        $candidates = [];

        $prioritySources = [
            $request->server('HTTP_CF_CONNECTING_IP') ?: $request->header('CF-Connecting-IP'),
            $request->server('HTTP_TRUE_CLIENT_IP') ?: $request->header('True-Client-IP'),
            $request->server('HTTP_X_REAL_IP') ?: $request->header('X-Real-IP'),
            $request->server('HTTP_X_FORWARDED_FOR') ?: $request->header('X-Forwarded-For'),
            $request->server('REMOTE_ADDR') ?: $request->ip(),
        ];

        foreach ($prioritySources as $raw) {
            if (empty($raw) || !is_string($raw)) {
                continue;
            }
            foreach (explode(',', $raw) as $part) {
                $candidate = trim($part);
                if ($candidate !== '') {
                    $candidates[] = $candidate;
                }
            }
        }

        foreach ($candidates as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    protected function extractCleanIp(string $ip): string
    {
        if (preg_match('/\(([0-9a-fA-F\.:]+)\)/', $ip, $m)) {
            return $m[1];
        }
        return trim($ip);
    }

    public function resolveHostname(string $ip, bool $forceInTests = false): ?string
    {
        $cleanIp = $this->extractCleanIp($ip);

        if ($this->isLocalIp($cleanIp)) {
            return null;
        }

        if (app()->runningUnitTests() && !$forceInTests) {
            return null;
        }

        try {
            $host = @gethostbyaddr($cleanIp);
            if (is_string($host) && $host !== '' && $host !== $cleanIp && !str_contains($host, ' ')) {
                return substr($host, 0, 255);
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

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

    protected function shouldQueryIpApi(): bool
    {
        if (!app()->runningUnitTests()) {
            return true;
        }

        try {
            $factory = Http::getFacadeRoot();
            $ref = new \ReflectionProperty($factory, 'stubCallbacks');
            $ref->setAccessible(true);
            $callbacks = $ref->getValue($factory);
            return $callbacks && $callbacks->isNotEmpty();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function resolveLocation(string $ip, Request $request): array
    {
        $clientLocation = trim((string) $request->input('client_location', ''));
        $clientDistrict = trim((string) $request->input('client_district', ''));
        $clientCity = trim((string) $request->input('client_city', ''));
        $clientRegion = trim((string) $request->input('client_region', ''));
        $clientCountry = trim((string) $request->input('client_country', ''));
        $clientIsp = trim((string) $request->input('client_isp', ''));
        $clientOrg = trim((string) $request->input('client_org', ''));
        $clientLat = $request->filled('client_latitude') && is_numeric($request->input('client_latitude'))
            ? (float) $request->input('client_latitude')
            : null;
        $clientLon = $request->filled('client_longitude') && is_numeric($request->input('client_longitude'))
            ? (float) $request->input('client_longitude')
            : null;

        $apiData = [];
        if ($this->shouldQueryIpApi()) {
            try {
                $cleanIp = $this->extractCleanIp($ip);
                $fields = 'status,country,countryCode,regionName,city,district,lat,lon,isp,org,as,mobile,proxy,hosting,timezone,query';
                $endpoint = $this->isLocalIp($cleanIp)
                    ? "http://ip-api.com/json/?fields={$fields}"
                    : "http://ip-api.com/json/{$cleanIp}?fields={$fields}";

                $response = Http::timeout(2.5)->get($endpoint);

                if ($response->successful()) {
                    $json = $response->json();
                    if (is_array($json) && ($json['status'] ?? '') === 'success') {
                        $apiData = $json;
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        $district = !empty($apiData['district']) ? $apiData['district'] : ($clientDistrict ?: null);
        $city = !empty($apiData['city']) ? $apiData['city'] : ($clientCity ?: null);
        $region = !empty($apiData['regionName']) ? $apiData['regionName'] : ($clientRegion ?: null);
        $country = !empty($apiData['country']) ? $apiData['country'] : ($clientCountry ?: null);
        $countryCode = $apiData['countryCode'] ?? null;
        $isp = !empty($apiData['isp']) ? $apiData['isp'] : ($clientIsp ?: null);
        $org = !empty($apiData['as'])
            ? $apiData['as']
            : (!empty($apiData['org']) ? $apiData['org'] : ($clientOrg ?: null));
        $mobile = isset($apiData['mobile']) ? (bool) $apiData['mobile'] : false;
        $proxy = isset($apiData['proxy']) ? (bool) $apiData['proxy'] : false;
        $hosting = isset($apiData['hosting']) ? (bool) $apiData['hosting'] : false;
        $timezone = $apiData['timezone'] ?? null;

        $resolvedIp = ($this->isLocalIp($ip) && !empty($apiData['query']))
            ? "{$ip} ({$apiData['query']})"
            : $ip;

        $hostname = $this->resolveHostname($resolvedIp);

        if (!empty($clientLocation)) {
            return [
                'ip' => $resolvedIp,
                'location' => $clientLocation,
                'district' => $district,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'countryCode' => $countryCode,
                'isp' => $isp,
                'org' => $org,
                'hostname' => $hostname,
                'mobile' => $mobile,
                'proxy' => $proxy,
                'hosting' => $hosting,
                'timezone' => $timezone,
                'latitude' => $clientLat,
                'longitude' => $clientLon,
            ];
        }

        if (!empty($apiData)) {
            $parts = array_filter([
                $district ? "Kec. {$district}" : null,
                $city,
                $region,
                $country,
            ]);
            $locationStr = !empty($parts) ? implode(', ', $parts) : 'Lokasi Terdeteksi';

            return [
                'ip' => $resolvedIp,
                'location' => $locationStr,
                'district' => $district,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'countryCode' => $countryCode,
                'isp' => $isp,
                'org' => $org,
                'hostname' => $hostname,
                'mobile' => $mobile,
                'proxy' => $proxy,
                'hosting' => $hosting,
                'timezone' => $timezone,
                'latitude' => $clientLat,
                'longitude' => $clientLon,
            ];
        }

        if ($clientLat !== null && $clientLon !== null) {
            return [
                'ip' => $ip,
                'location' => sprintf('Koordinat GPS (%.4f, %.4f)', $clientLat, $clientLon),
                'district' => $district,
                'city' => null,
                'region' => null,
                'country' => 'Indonesia',
                'countryCode' => 'ID',
                'isp' => $isp,
                'org' => $org,
                'hostname' => $hostname,
                'mobile' => $mobile,
                'proxy' => $proxy,
                'hosting' => $hosting,
                'timezone' => $timezone,
                'latitude' => $clientLat,
                'longitude' => $clientLon,
            ];
        }

        $cfCountry = $request->header('CF-IPCountry');
        if (!empty($cfCountry) && $cfCountry !== 'XX') {
            return [
                'ip' => $ip,
                'location' => $cfCountry === 'ID' ? 'Indonesia' : "Negara: {$cfCountry}",
                'district' => $district,
                'city' => null,
                'region' => null,
                'country' => $cfCountry === 'ID' ? 'Indonesia' : $cfCountry,
                'countryCode' => $cfCountry,
                'isp' => $isp,
                'org' => $org,
                'hostname' => $hostname,
                'mobile' => $mobile,
                'proxy' => $proxy,
                'hosting' => $hosting,
                'timezone' => $timezone,
                'latitude' => null,
                'longitude' => null,
            ];
        }

        return [
            'ip' => $ip,
            'location' => $this->isLocalIp($ip) ? 'Jaringan Lokal (Localhost)' : 'Indonesia',
            'district' => $district,
            'city' => $this->isLocalIp($ip) ? 'Localhost' : null,
            'region' => null,
            'country' => 'Indonesia',
            'countryCode' => 'ID',
            'isp' => $isp,
            'org' => $org,
            'hostname' => $hostname,
            'mobile' => $mobile,
            'proxy' => $proxy,
            'hosting' => $hosting,
            'timezone' => $timezone,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    public function parseUserAgent(string $ua): array
    {
        if (empty($ua)) {
            return [
                'browser' => 'Unknown Browser',
                'os' => 'Unknown OS',
                'device_type' => 'Desktop',
                'device_model' => null,
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

        $deviceModel = null;
        if (preg_match('/Android\s+[0-9\.]+;\s*([^;\)]+?)(?:\s+Build\/|\))/i', $ua, $m)) {
            $candidate = trim($m[1]);
            if (!in_array(strtoupper($candidate), ['K', 'MOBILE', 'LINUX', 'U', 'WV'], true) && strlen($candidate) >= 2) {
                $deviceModel = $candidate;
            }
        } elseif (preg_match('/\((iPhone|iPad|iPod)/i', $ua, $m)) {
            $deviceModel = $m[1];
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device_type' => $deviceType,
            'device_model' => $deviceModel,
        ];
    }
}
