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

    // Jonli efirning maksimal davomiyligi (5 soat)
    public const MAX_DURATION_HOURS = 5;

    protected $fillable = [
        'book_id',
        'host_user_id',
        'group_id',
        'title',
        'description',
        'cover',
        'scheduled_at',
        'started_at',
        'stream_url',
        'status',
        'permission_mode',
        'is_recording',
        'replay_url',
        'likes_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at'   => 'datetime',
        'is_recording' => 'boolean',
        'likes_count'  => 'integer',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LiveQuestion::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(\App\Models\LiveSignal::class);
    }

    public function eventLikes(): HasMany
    {
        return $this->hasMany(LiveEventLike::class);
    }

    public function getCurrentViewersAttribute(): int
    {
        if ($this->status !== self::STATUS_LIVE) {
            return 0;
        }

        $cached = \Illuminate\Support\Facades\Cache::get("live_event_{$this->id}_viewers");
        if ($cached !== null) {
            return (int) $cached;
        }

        $signal = \App\Models\LiveSignal::where('live_event_id', $this->id)
            ->where('type', 'viewer-count')
            ->where('created_at', '>', now()->subSeconds(30))
            ->latest('id')
            ->value('payload');

        if ($signal) {
            $decoded = is_array($signal) ? $signal : json_decode($signal, true);
            return (int) ($decoded['count'] ?? 0);
        }

        return 0;
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
        $cutoff = now()->subHours(self::MAX_DURATION_HOURS);

        return $query->where('status', self::STATUS_LIVE)
                     ->where(function ($q) use ($cutoff) {
                         $q->where('started_at', '>', $cutoff)
                           ->orWhere(function ($sub) use ($cutoff) {
                               $sub->whereNull('started_at')
                                   ->where('created_at', '>', $cutoff);
                           });
                     });
    }

    /**
     * Efir 5 soatlik maksimal vaqt chegarasiga yetgan yoki undan oshganligini tekshirish.
     */
    public function hasExceededMaxDuration(): bool
    {
        if ($this->status !== self::STATUS_LIVE) {
            return false;
        }

        $startTime = $this->started_at ?? $this->created_at;
        if (!$startTime) {
            return false;
        }

        return $startTime->copy()->addHours(self::MAX_DURATION_HOURS)->isPast();
    }

    /**
     * 5 soatlik limitgacha qolgan soniyalar miqdori.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if ($this->status !== self::STATUS_LIVE) {
            return 0;
        }

        $startTime = $this->started_at ?? $this->created_at;
        if (!$startTime) {
            return self::MAX_DURATION_HOURS * 3600;
        }

        $deadline = $startTime->copy()->addHours(self::MAX_DURATION_HOURS);
        return max(0, now()->diffInSeconds($deadline, false));
    }

    /**
     * Jonli efirni avtomatik yoki qo'lda yakunlab, barcha aloqador yozuvlarini tozalab, bazadan o'chirish.
     */
    public function endAndCleanup(?string $reason = null): void
    {
        // Tomoshabinlarga efir yakunlangani haqida oxirgi signalni qoldiramiz
        try {
            LiveSignal::create([
                'live_event_id' => $this->id,
                'sender_id'     => 'server_system',
                'receiver_id'   => 'all',
                'type'          => 'stream-status',
                'payload'       => json_encode([
                    'active' => false,
                    'ended'  => true,
                    'reason' => $reason ?? 'Jonli efir 5 soatlik maksimal vaqtga yetdi va avtomatik yakunlandi.',
                ]),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {}

        try {
            $this->questions()->delete();
        } catch (\Throwable $e) {}

        try {
            $this->signals()->delete();
        } catch (\Throwable $e) {}

        try {
            $this->eventLikes()->delete();
        } catch (\Throwable $e) {}

        \Illuminate\Support\Facades\Cache::forget("live_event_{$this->id}_viewers");

        $this->delete();
    }

    /**
     * 5 soatdan ortiq davom etgan barcha faol efirlarni avtomatik topib, darhol yakunlaydi va tozalaydi.
     */
    public static function terminateExpiredLiveStreams(): int
    {
        $cutoff = now()->subHours(self::MAX_DURATION_HOURS);

        $expired = self::where('status', self::STATUS_LIVE)
            ->where(function ($q) use ($cutoff) {
                $q->where('started_at', '<=', $cutoff)
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNull('started_at')
                          ->where('created_at', '<=', $cutoff);
                  });
            })
            ->get();

        $count = 0;
        foreach ($expired as $event) {
            $event->endAndCleanup('Maksimal efir vaqti (5 soat) tugadi va efir avtomatik yakunlandi.');
            $count++;
        }

        return $count;
    }

    public function scopeEnded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENDED)->latest('scheduled_at');
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('group_id');
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Whether this event belongs to a group.
     */
    public function getIsGroupEventAttribute(): bool
    {
        return !is_null($this->group_id);
    }

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
