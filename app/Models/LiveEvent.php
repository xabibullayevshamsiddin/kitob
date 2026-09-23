<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class LiveEvent extends Model
{
    use HasFactory;

    // Possible status values
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE      = 'live';
    public const STATUS_ENDED     = 'ended';

    protected $fillable = [
        'book_id',
        'host_user_id',
        'title',
        'description',
        'cover',
        'scheduled_at',
        'stream_url',
        'status',
        'permission_mode',
        'is_recording',
        'replay_url',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_recording' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function hostUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LiveQuestion::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                     ->where('scheduled_at', '>', now())
                     ->orderBy('scheduled_at');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LIVE);
    }

    public function scopeEnded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENDED)->latest('scheduled_at');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Whether the event is currently streaming.
     */
    public function getIsLiveAttribute(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    /**
     * Returns a human-readable countdown string (e.g. "2 days", "3 hours")
     * or "Live now" / "Ended" depending on the event state.
     */
    public function getCountdownAttribute(): string
    {
        if ($this->status === self::STATUS_LIVE) {
            return 'Live now';
        }

        if ($this->status === self::STATUS_ENDED) {
            return 'Ended';
        }

        if (!$this->scheduled_at) {
            return 'TBA';
        }

        $diff = now()->diffAsCarbonInterval($this->scheduled_at, false);

        if ($diff->totalSeconds <= 0) {
            return 'Starting soon';
        }

        return Carbon::now()->diffForHumans($this->scheduled_at, [
            'syntax' => Carbon::DIFF_ABSOLUTE,
            'parts'  => 2,
        ]);
    }
}
