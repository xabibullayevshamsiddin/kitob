<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_event_id',
        'user_id',
        'question',
        'is_priority',
        'is_selected',
        'is_answered',
        'likes_count',
    ];

    protected $casts = [
        'is_priority' => 'boolean',
        'is_selected' => 'boolean',
        'is_answered' => 'boolean',
        'likes_count' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function liveEvent(): BelongsTo
    {
        return $this->belongsTo(LiveEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    public function scopePriority(Builder $query): Builder
    {
        return $query->where('is_priority', true);
    }

    /**
     * Savollar navbati: ustuvorlar yuqorida, keyin likes_count, keyin vaqt bo'yicha.
     */
    public function scopeInPriorityOrder(Builder $query): Builder
    {
        return $query->orderByDesc('is_priority')
            ->orderByDesc('likes_count')
            ->orderBy('created_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_answered', false)->where('is_selected', true);
    }
}
