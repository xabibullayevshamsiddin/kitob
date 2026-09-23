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
        'is_selected',
        'is_answered',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
        'is_answered' => 'boolean',
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_answered', false)->where('is_selected', true);
    }
}
