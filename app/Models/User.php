<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
        'last_login_ip',
        'last_login_location',
        'last_login_browser',
        'last_login_os',
        'last_login_latitude',
        'last_login_longitude',
    ];

protected $hidden = [
        'password',
        'remember_token',
    ];

protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'last_login_latitude' => 'float',
        'last_login_longitude' => 'float',
    ];

public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
