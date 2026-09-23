<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_date',
        'minutes_read',
        'logged_in',
        'points_earned',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'logged_in'     => 'boolean',
        'minutes_read'  => 'integer',
        'points_earned' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('activity_date', today());
    }

    public function scopeLastDays(Builder $query, int $days): Builder
    {
        return $query->whereDate('activity_date', '>=', today()->subDays($days));
    }
}
