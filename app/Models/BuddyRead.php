<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuddyRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'creator_id',
        'title',
        'status',
        'max_members',
        'is_private',
        'password',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function checkPassword(?string $inputPassword): bool
    {
        if (!$this->is_private || empty($this->password)) {
            return true;
        }
        if (empty($inputPassword)) {
            return false;
        }
        return \Illuminate\Support\Facades\Hash::check($inputPassword, $this->password) || $this->password === trim($inputPassword);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(BuddyReadMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BuddyReadMessage::class);
    }

    public function isMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
