<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'author',
        'description',
        'cover_image',
        'pdf_path',
        'genre',
        'week_number',
        'published_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active'    => 'boolean',
        'week_number'  => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function chapters(): HasMany
    {
        return $this->hasMany(BookChapter::class)->orderBy('chapter_number');
    }

    public function audios(): HasMany
    {
        return $this->hasMany(BookAudio::class);
    }

    public function musics(): HasMany
    {
        return $this->hasMany(BookMusic::class)->orderBy('order')->orderBy('id');
    }

    public function activeMusics(): HasMany
    {
        return $this->hasMany(BookMusic::class)->where('is_active', true)->orderBy('order')->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(BookVideo::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dailyQuotes(): HasMany
    {
        return $this->hasMany(DailyQuote::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function liveEvents(): HasMany
    {
        return $this->hasMany(LiveEvent::class);
    }

    public function readingSessions(): HasMany
    {
        return $this->hasMany(ReadingSession::class);
    }

    /**
     * All reading progress records for this book (through chapters).
     */
    public function readingProgress(): HasMany
    {
        return $this->hasMany(BookReadingProgress::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Foydalanuvchi huquqi va reytingiga asosan ko'rinadigan kitoblar.
     * Top 5 foydalanuvchilarga rasmiy e'londan 24 soat oldin (erta kirish) ochiladi.
     */
    public function scopeAvailableForUser(Builder $query, ?\App\Models\User $user = null): Builder
    {
        $query->where('is_active', true);

        if ($user && ($user->isAdmin() || (method_exists($user, 'hasRole') && $user->hasRole('admin')))) {
            return $query;
        }

        $isTopFive = false;
        if ($user) {
            $rank = app(\App\Services\LeaderboardService::class)->getUserRank($user);
            $isTopFive = ($rank !== null && $rank <= 5);
        }

        if ($isTopFive) {
            return $query->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now()->addHours(24));
            });
        }

        return $query->where(function ($q) {
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', now());
        });
    }

    public function scopeCurrentWeek(Builder $query): Builder
    {
        return $query->where('week_number', Carbon::now()->weekOfYear);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns full public URL for the book cover image.
     */
    public function getCoverUrlAttribute(): string
    {
        if (!$this->cover_image) {
            return asset('images/book-placeholder.png');
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        if (str_starts_with($this->cover_image, 'storage/')) {
            return asset($this->cover_image);
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * Foydalanuvchi ushbu kitobga test qo'sha olishini tekshiradi:
     * - Admin har qanday kitobga test qo'sha oladi (admin bundan mustasno)
     * - O'qituvchi faqat o'zi saytga qo'shgan kitobga test qo'sha oladi
     */
    public function canUserAddQuiz(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || ($user->role === 'admin')) {
            return true;
        }

        if (($user->isTeacher() || ($user->role === 'teacher')) && $this->created_by && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        return false;
    }
}

