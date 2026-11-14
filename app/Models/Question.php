<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

protected $fillable = [
        'subtest_id',
        'number',
        'type',
        'text',
        'image',
        'explanation',
    ];

protected $casts = [
        'number' => 'integer',
        'type' => 'string',
    ];

public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('label');
    }

public function isMultiple(): bool
    {
        return $this->type === 'multiple';
    }

public function isSingle(): bool
    {
        return $this->type === 'single';
    }

public function isStatement(): bool
    {
        return $this->type === 'statement';
    }

public function hasImage(): bool
    {
        return !empty($this->image);
    }

public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
