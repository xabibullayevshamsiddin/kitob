<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_active_date',
    ];

    protected $casts = [
        'last_active_date' => 'date',
        'current_streak'   => 'integer',
        'longest_streak'   => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Whether the user has already performed a reading activity today
     * (evaluated in Asia/Tashkent timezone).
     */
    public function getIsActiveTodayAttribute(): bool
    {
        if (!$this->last_active_date) {
            return false;
        }

        $todayTashkent = Carbon::now('Asia/Tashkent')->toDateString();

        return $this->last_active_date->toDateString() === $todayTashkent;
    }

    /**
     * Returns how many hours remain before the streak is lost.
     * A streak is considered lost if no activity is recorded by end of tomorrow.
     */
    public function getDaysUntilLostAttribute(): int
    {
        if (!$this->last_active_date || $this->current_streak === 0) {
            return 0;
        }

        $nowTashkent      = Carbon::now('Asia/Tashkent');
        $lastActiveTashkent = Carbon::parse($this->last_active_date)->timezone('Asia/Tashkent');

        $diffInDays = $nowTashkent->diffInDays($lastActiveTashkent, false);

        // Streak expires if user misses today (diff > 1 day)
        if ($diffInDays <= -2) {
            return 0;
        }

        return (int) max(0, $lastActiveTashkent->addDays(2)->diffInDays($nowTashkent));
    }
}
