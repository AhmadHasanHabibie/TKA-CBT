<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subtest_id',
        'number',
        'type',
        'text',
        'image',
        'explanation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number' => 'integer',
        'type' => 'string',
    ];

    /**
     * Subtest that this question belongs to.
     */
    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

    /**
     * Options available for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('label');
    }

    /**
     * Check if this question is a Pilihan Ganda Kompleks (multiple correct answers).
     */
    public function isMultiple(): bool
    {
        return $this->type === 'multiple';
    }

    /**
     * Check if this question is a standard single-choice question.
     */
    public function isSingle(): bool
    {
        return $this->type === 'single';
    }

    /**
     * Check if this question is a Sesuai / Tidak Sesuai (statement evaluation) question.
     */
    public function isStatement(): bool
    {
        return $this->type === 'statement';
    }

    /**
     * Check if this question has an associated image.
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * Get public image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
