<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSignal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'live_event_id',
        'sender_id',
        'receiver_id',
        'type',
        'payload',
        'created_at',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LiveEvent::class, 'live_event_id');
    }
}
