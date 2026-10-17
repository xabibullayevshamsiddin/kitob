<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'message',
        'response',
        'provider',
        'model',
        'tokens_used',
        'sources',
        'refused',
        'refusal_reason',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
        'top_score',
    ];

    protected $casts = [
        'tokens_used'       => 'integer',
        'sources'           => 'array',
        'refused'           => 'boolean',
        'prompt_tokens'     => 'integer',
        'completion_tokens' => 'integer',
        'latency_ms'        => 'integer',
        'top_score'         => 'float',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Book is optional – some AI conversations are not book-specific.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withDefault();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Returns latest N records (default 20), ordered oldest first for chat display.
     */
    public function scopeRecent(Builder $query, int $limit = 20): Builder
    {
        return $query->latest()->limit($limit);
    }

    public function scopeForBook(Builder $query, int $bookId): Builder
    {
        return $query->where('book_id', $bookId);
    }
}
