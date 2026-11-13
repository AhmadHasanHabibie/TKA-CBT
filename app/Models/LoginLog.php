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
        'visited_url',
        'ip_address',
        'location',
        'city',
        'region',
        'country',
        'latitude',
        'longitude',
        'browser',
        'os',
        'device_type',
        'user_agent',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the user that owns the login log (nullable for guest link clicks).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get Google Maps URL if coordinates or location exist.
     */
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
}
