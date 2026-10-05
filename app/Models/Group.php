<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'book_id',
        'created_by',
        'cover_image',
        'is_private',
        'password',
        'invite_code',
        'max_members',
        'chat_enabled',
        'voice_enabled',
    ];

    protected $casts = [
        'is_private'    => 'boolean',
        'max_members'   => 'integer',
        'chat_enabled'  => 'boolean',
        'voice_enabled' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('role', 'joined_at');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_private', true);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the current member count for this group.
     */
    public function getMembersCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Returns full cover image URL or null if not set.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : null;
    }

    /**
     * Checks if a user has management permissions (creator or system admin).
     */
    public function isManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin')) || ($user->role === 'admin');

        return $this->created_by === $user->id || $isAdmin;
    }

    /**
     * Checks if a user is allowed to post messages in this group.
     */
    public function canUserChat(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isManagedBy($user)) {
            return true;
        }

        return (bool) ($this->chat_enabled ?? true);
    }

    /**
     * Checks if a user is allowed to send voice messages in this group.
     */
    public function canUserSendVoice(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isManagedBy($user)) {
            return true;
        }

        return (bool) ($this->voice_enabled ?? true);
    }
}
