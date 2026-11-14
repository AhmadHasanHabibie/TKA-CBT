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
        'school_name',
        'slug',
        'category',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

public function items(): HasMany
    {
        return $this->hasMany(QuestionBankItem::class)->orderBy('order')->orderBy('id');
    }

public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
