<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuddyReadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'buddy_read_id',
        'user_id',
        'message',
        'page_number',
        'is_spoiler',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'is_spoiler' => 'boolean',
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
