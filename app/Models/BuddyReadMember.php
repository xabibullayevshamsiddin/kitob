<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuddyReadMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'buddy_read_id',
        'user_id',
        'current_page',
        'voice_active',
        'last_voice_at',
    ];

    protected $casts = [
        'voice_active' => 'boolean',
        'last_voice_at' => 'datetime',
    ];

    public function buddyRead(): BelongsTo
    {
        return $this->belongsTo(BuddyRead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
