<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    use HasFactory;

protected $fillable = [
        'user_id',
        'subtest_id',
        'started_at',
        'ends_at',
        'finished_at',
        'status',
        'score',
        'correct_count',
        'tab_violation_count',
    ];

protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'decimal:2',
        'correct_count' => 'integer',
        'tab_violation_count' => 'integer',
    ];

public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->ends_at);
    }

public function remainingSeconds(): int
    {
        return max(0, $this->ends_at->timestamp - now()->timestamp);
    }
}
