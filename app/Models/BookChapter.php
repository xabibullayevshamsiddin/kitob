<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookChapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'chapter_number',
        'title',
        'content',
        'duration_minutes',
        'is_published',
    ];

    protected $casts = [
        'is_published'    => 'boolean',
        'chapter_number'  => 'integer',
        'duration_minutes'=> 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function audios(): HasMany
    {
        return $this->hasMany(BookAudio::class, 'chapter_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'chapter_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class, 'chapter_id');
    }

    public function readingSessions(): HasMany
    {
        return $this->hasMany(ReadingSession::class, 'chapter_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
