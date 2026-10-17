<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->orderBy('sort_order')
                     ->latest('id');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        if ($category === 'all' || empty($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'books'   => 'Kitoblar va Mutolaa',
            'points'  => 'Ballar va Tangalar',
            'groups'  => 'Guruhlar',
            'ai'      => 'AI Suhbatdosh',
            default   => 'Umumiy savollar',
        };
    }

    public function getCategoryBadgeClassesAttribute(): string
    {
        return match ($this->category) {
            'books'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'points'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'groups'  => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
            'ai'      => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
            default   => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        };
    }
}
