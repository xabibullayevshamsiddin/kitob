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

        if (!$path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::url($path);
    }

    /**
     * Returns thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->title) . '&background=4f46e5&color=fff&size=512';
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        return Storage::url($this->thumbnail);
    }

    /**
     * Returns an embeddable URL for YouTube or Vimeo, or null for direct video files.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $url = $this->video_path;
        if (!$url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $match)) {
            return 'https://www.youtube.com/embed/' . $match[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $match)) {
            return 'https://player.vimeo.com/video/' . $match[1];
        }

        return null;
    }
}
