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
        return $this->belongsTo(Book::class);
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

        try {
            return Storage::disk('s3')->temporaryUrl(
                $this->file_path,
                now()->addMinutes(60)
            );
        } catch (\RuntimeException) {
            // Local disk – return standard storage URL
            return Storage::url($this->file_path);
        }
    }
}
