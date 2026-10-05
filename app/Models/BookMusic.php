<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookMusic extends Model
{
    use HasFactory;

    protected $table = 'book_musics';

    protected $fillable = [
        'book_id',
        'title',
        'file_path',
        'duration',
        'order',
        'is_active',
    ];

    protected $casts = [
        'duration'  => 'integer',
        'order'     => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'file_url',
        'formatted_duration',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . $this->file_path);
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration || $this->duration <= 0) {
            return '--:--';
        }

        $min = floor($this->duration / 60);
        $sec = $this->duration % 60;

        return sprintf('%02d:%02d', $min, $sec);
    }
}
