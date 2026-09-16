<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subtest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_minutes',
        'total_questions',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_minutes' => 'integer',
        'total_questions' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot logic for auto-generating slug.
     */
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

    /**
     * Questions belonging to this subtest.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('number');
    }

    /**
     * Exam sessions associated with this subtest.
     */
    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    /**
     * Sync and update total questions count automatically.
     */
    public function updateQuestionCount(): void
    {
        $this->update([
            'total_questions' => $this->questions()->count(),
        ]);
    }
}
