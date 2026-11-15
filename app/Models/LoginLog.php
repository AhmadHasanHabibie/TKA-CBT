<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'event_type',
        'attempted_email',
        'school_name',
        'visited_url',
        'ip_address',
        'location',
        'district',
        'city',
        'region',
        'country',
        'isp',
        'org',
        'hostname',
        'is_mobile_network',
        'is_proxy',
        'is_hosting',
        'is_vpn_suspected',
        'ip_timezone',
        'client_timezone',
        'latitude',
        'longitude',
        'browser',
        'os',
        'device_type',
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
        'user_agent',
        'telemetry_data',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_mobile_network' => 'boolean',
        'is_proxy' => 'boolean',
        'is_hosting' => 'boolean',
        'is_vpn_suspected' => 'boolean',
        'cpu_cores' => 'integer',
        'ram_gb' => 'float',
        'battery_level' => 'integer',
        'battery_charging' => 'boolean',
        'network_downlink' => 'float',
        'network_rtt' => 'integer',
        'telemetry_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMapUrlAttribute(): ?string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        if ($this->location && !str_contains(strtolower($this->location), 'localhost') && !str_contains(strtolower($this->location), 'tidak diketahui')) {
            return 'https://www.google.com/maps/search/' . urlencode($this->location);
        }

        return null;
    }

    public function getIsTimezoneMismatchAttribute(): bool
    {
        if (empty($this->client_timezone)) {
            return false;
        }

        $indonesianTimezones = [
            'Asia/Jakarta',
            'Asia/Pontianak',
            'Asia/Makassar',
            'Asia/Ujung_Pandang',
            'Asia/Jayapura',
        ];

        $clientInId = in_array($this->client_timezone, $indonesianTimezones, true);

        if (!empty($this->ip_timezone)) {
            $ipInId = in_array($this->ip_timezone, $indonesianTimezones, true);
            if ($clientInId !== $ipInId) {
                return true;
            }
            if (!$clientInId && !$ipInId && $this->client_timezone !== $this->ip_timezone) {
                return true;
            }
        }

        if ($clientInId && !empty($this->country) && !in_array(strtolower($this->country), ['indonesia', 'id'], true)) {
            return true;
        }

        return false;
    }
}
