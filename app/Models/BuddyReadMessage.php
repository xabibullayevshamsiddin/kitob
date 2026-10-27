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
        'audio_path',
        'audio_duration',
        'page_number',
        'is_spoiler',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'is_spoiler' => 'boolean',
        'audio_duration' => 'integer',
    ];

    public function getAudioUrlAttribute(): ?string
    {
        if (empty($this->audio_path)) return null;
        if (str_starts_with($this->audio_path, 'http')) return $this->audio_path;
        return asset('storage/' . $this->audio_path);
    }

    public function buddyRead(): BelongsTo
    {
        return $this->belongsTo(BuddyRead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
