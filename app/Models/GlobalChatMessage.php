<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'audio_path',
        'audio_duration',
        'reply_to_id',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted'     => 'boolean',
        'audio_duration' => 'integer',
    ];

    public function getAudioUrlAttribute(): ?string
    {
        if (!$this->audio_path) {
            return null;
        }
        return asset('storage/' . $this->audio_path);
    }

    public function getIsVoiceAttribute(): bool
    {
        return !empty($this->audio_path) || str_starts_with($this->message ?? '', '[VOICE:');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The message being replied to – only loads id, message and user_id
     * to avoid exposing full content of deleted messages.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(GlobalChatMessage::class, 'reply_to_id')
                    ->select(['id', 'message', 'user_id']);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Returns the latest N messages (default 50), newest last.
     */
    public function scopeRecent(Builder $query, int $limit = 50): Builder
    {
        return $query->latest()->limit($limit);
    }
}
