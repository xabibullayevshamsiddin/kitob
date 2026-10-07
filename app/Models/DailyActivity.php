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
        'hourly_bonus_claimed',
        'early_bird_claimed',
    ];

    protected $casts = [
        'activity_date'        => 'date',
        'logged_in'            => 'boolean',
        'minutes_read'         => 'integer',
        'points_earned'        => 'integer',
        'hourly_bonus_claimed' => 'boolean',
        'early_bird_claimed'   => 'boolean',
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

    /**
     * SQLite va MySQL uchun activity_date ni doim 'Y-m-d' formatida saqlash
     */
    public function fromDateTime($value)
    {
        return empty($value) ? $value : $this->asDateTime($value)->format('Y-m-d');
    }

    /**
     * Foydalanuvchining bugungi faolligini topish yoki xavfsiz yaratish
     */
    public static function getTodayActivity(int $userId, ?string $date = null): self
    {
        $date = $date ?: now('Asia/Tashkent')->toDateString();

        $activity = static::where('user_id', $userId)
            ->whereDate('activity_date', $date)
            ->first();

        if (!$activity) {
            $activity = static::firstOrCreate(
                ['user_id' => $userId, 'activity_date' => $date],
                ['minutes_read' => 0, 'logged_in' => true, 'points_earned' => 0, 'hourly_bonus_claimed' => false]
            );
        }

        return $activity;
    }
}
