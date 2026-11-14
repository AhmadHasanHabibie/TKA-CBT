<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subtest extends Model
{
    use HasFactory;

protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_minutes',
        'total_questions',
        'is_active',
    ];

protected $casts = [
        'duration_minutes' => 'integer',
        'total_questions' => 'integer',
        'is_active' => 'boolean',
    ];

protected static function boot()
    {
        parent::boot();

        static::creating(function ($subtest) {
            if (empty($subtest->slug)) {
                $baseSlug = Str::slug($subtest->name);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }
                $subtest->slug = $slug;
            }
        });
    }

public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('number');
    }

public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

public function updateQuestionCount(): void
    {
        $this->update([
            'total_questions' => $this->questions()->count(),
        ]);
    }
}
