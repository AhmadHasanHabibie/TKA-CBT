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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_session_id',
        'question_id',
        'is_doubt',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_doubt' => 'boolean',
    ];

    /**
     * The exam session this answer belongs to.
     */
    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * The question being answered.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The individual option records associated with this answer.
     */
    public function userAnswerOptions(): HasMany
    {
        return $this->hasMany(UserAnswerOption::class);
    }

    /**
     * The options chosen by the student (supports both single and multiple choices).
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'user_answer_options')->withPivot('value')->withTimestamps();
    }
}
