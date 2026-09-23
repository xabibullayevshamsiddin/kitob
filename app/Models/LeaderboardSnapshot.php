<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LeaderboardSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'user_id',
        'rank',
        'points',
        'reading_minutes',
        'quiz_score',
        'streak_days',
        'snapshot_date',
    ];

    protected $casts = [
        'snapshot_date'   => 'date',
        'rank'            => 'integer',
        'points'          => 'integer',
        'reading_minutes' => 'integer',
        'quiz_score'      => 'integer',
        'streak_days'     => 'integer',
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

    /**
     * Filter by period: 'daily', 'weekly', 'monthly', 'all_time'
     */
    public function scopePeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }

    public function scopeDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('snapshot_date', $date);
    }

    public function scopeTopN(Builder $query, int $n = 10): Builder
    {
        return $query->orderBy('rank')->limit($n);
    }
}
