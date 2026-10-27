<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuddyReadVoiceSignal extends Model
{
    use HasFactory;

    protected $fillable = [
        'buddy_read_id',
        'from_user_id',
        'to_user_id',
        'type',
        'payload',
        'is_consumed',
    ];

    protected $casts = [
        'is_consumed' => 'boolean',
    ];

    public function buddyRead(): BelongsTo
    {
        return $this->belongsTo(BuddyRead::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
