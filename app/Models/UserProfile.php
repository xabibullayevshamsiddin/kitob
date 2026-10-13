<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reading_place',
        'reading_goal',
        'avatar_frame',
        'profile_theme',
        'chat_style_preference',
        'unlocked_items',
        'chat_bubble_theme',
        'privacy_settings',
    ];

    protected $casts = [
        'privacy_settings' => 'array',
        'unlocked_items' => 'array',
    ];

    public function hasUnlockedItem(string $item): bool
    {
        $items = $this->unlocked_items ?? [];
        return in_array($item, $items, true);
    }

    public function unlockItem(string $item): void
    {
        $items = $this->unlocked_items ?? [];
        if (!in_array($item, $items, true)) {
            $items[] = $item;
            $this->unlocked_items = $items;
        }
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
