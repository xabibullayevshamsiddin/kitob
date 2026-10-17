<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'parent_id',
        'page_number',
        'rating',
        'comment',
        'likes_count',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'rating'      => 'integer',
        'likes_count' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BookComment::class, 'parent_id')->with(['user', 'likes'])->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(BookCommentLike::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function canDelete(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($user->isAdmin() || (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            return true;
        }

        return false;
    }
}
