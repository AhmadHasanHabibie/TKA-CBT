<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAnswer extends Model
{
    use HasFactory;

protected $fillable = [
        'exam_session_id',
        'question_id',
        'is_doubt',
    ];

protected $casts = [
        'is_doubt' => 'boolean',
    ];

public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

public function userAnswerOptions(): HasMany
    {
        return $this->hasMany(UserAnswerOption::class);
    }

public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'user_answer_options')->withPivot('value')->withTimestamps();
    }
}
