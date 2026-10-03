<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveEventLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_event_id',
        'user_id',
    ];

    public function liveEvent(): BelongsTo
    {
        return $this->belongsTo(LiveEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
