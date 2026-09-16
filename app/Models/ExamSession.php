<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'decimal:2',
        'correct_count' => 'integer',
        'tab_violation_count' => 'integer',
    ];

    /**
     * User taking the exam session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subtest associated with this session.
     */
    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

    /**
     * User answers recorded in this session.
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    /**
     * Check if exam session time has expired.
     */
    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->ends_at);
    }

    /**
     * Get remaining duration in seconds.
     */
    public function remainingSeconds(): int
    {
        return max(0, $this->ends_at->timestamp - now()->timestamp);
    }
}
