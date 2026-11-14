<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswerOption extends Model
{
    use HasFactory;

protected $fillable = [
        'user_answer_id',
        'option_id',
        'value',
    ];

protected $casts = [
        'value' => 'boolean',
    ];

public function userAnswer(): BelongsTo
    {
        return $this->belongsTo(UserAnswer::class);
    }

public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
