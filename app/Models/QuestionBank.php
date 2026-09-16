<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model to auto-generate unique slugs if empty.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bank) {
            if (empty($bank->slug)) {
                $baseSlug = Str::slug($bank->title);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-" . (++$count);
                }
                $bank->slug = $slug;
            }
        });
    }

    /**
     * Items (photos/questions) in this bank.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuestionBankItem::class)->orderBy('order')->orderBy('id');
    }

    /**
     * Admin user who created this bank.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active question banks (visible to students).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
