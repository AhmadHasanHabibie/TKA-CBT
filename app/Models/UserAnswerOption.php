<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswerOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_answer_id',
        'option_id',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'boolean',
    ];

    /**
     * The parent user answer record.
     */
    public function userAnswer(): BelongsTo
    {
        return $this->belongsTo(UserAnswer::class);
    }

    /**
     * The selected option.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
