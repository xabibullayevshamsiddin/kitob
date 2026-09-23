<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'comment',
        'parent_id',
        'likes_count',
    ];

    protected $casts = [
        'likes_count' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parent comment (one level up).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_id');
    }

    /**
     * Direct child replies.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'parent_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}
