<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookAudio extends Model
{
    use HasFactory;

    /**
     * Laravel's pluralizer treats "audio" as uncountable,
     * so we pin the table name to the migration's `book_audios`.
     */
    protected $table = 'book_audios';

    protected $fillable = [
        'book_id',
        'chapter_id',
        'file_path',
        'duration',
        'title',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withDefault([
            'title' => 'Mustaqil audio',
            'slug'  => '',
        ]);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(BookChapter::class, 'chapter_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns a time-limited signed URL for the audio file.
     * Falls back to a regular public URL when the disk doesn't support signing.
     */
    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        if (str_starts_with($this->file_path, 'storage/')) {
            return asset($this->file_path);
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}
