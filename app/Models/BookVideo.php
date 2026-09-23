<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'type',
        'chapter_number',
        'title',
        'video_path',
        'thumbnail',
        'hls_path',
        'duration',
        'is_processed',
    ];

    protected $casts = [
        'is_processed'   => 'boolean',
        'chapter_number' => 'integer',
        'duration'       => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('is_processed', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the HLS playlist URL if processed, otherwise the raw video URL.
     */
    public function getStreamUrlAttribute(): string
    {
        $path = $this->is_processed && $this->hls_path
            ? $this->hls_path
            : $this->video_path;

        return $path ? Storage::url($path) : '';
    }

    /**
     * Returns thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? Storage::url($this->thumbnail)
            : asset('images/video-placeholder.png');
    }
}
