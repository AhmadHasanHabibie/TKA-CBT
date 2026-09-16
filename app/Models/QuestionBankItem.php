<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'image',
        'title',
        'notes',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * The question bank this item belongs to.
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    /**
     * Get the public URL for the item image.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
}
