<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'ai_enabled',
        'ai_index_status',
        'ai_indexed_at',
        'ai_summary',
        'ai_text_quality',
        'ai_index_error',
        'ai_pdf_content_hash',
        'mood',
    ];

    protected $casts = [
        'published_at'    => 'datetime',
        'is_active'       => 'boolean',
        'week_number'     => 'integer',
        'ai_enabled'      => 'boolean',
        'ai_indexed_at'   => 'datetime',
        'ai_text_quality' => 'float',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('kitobxon_sitemap_xml');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('kitobxon_sitemap_xml');
        });
    }

    /**
     * Kitob mazmunini (bob, PDF, audio) ko'rish huquqi — books.show sahifasidagi qoidalar bilan bir xil:
     * nofaol kitobni faqat admin yoki muallif ko'radi, e'lon qilinmaganini esa Top 5 kitobxonlar ham.
     */
    public function isViewableBy(?User $user): bool
    {
        if ($user && ($user->isAdmin() || (int) $this->created_by === (int) $user->id)) {
            return true;
        }

        if (!$this->is_active) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            $rank = $user ? app(\App\Services\LeaderboardService::class)->getUserRank($user) : null;

            return $rank !== null && $rank <= 5;
        }

        return true;
    }

    /**
     * Retrieve the model for a bound value (supports both ID and slug).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function chapters(): HasMany
    {
        return $this->hasMany(BookChapter::class)->orderBy('chapter_number');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(BookPage::class)->orderBy('page_number');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(BookChunk::class)->orderBy('ordinal');
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

    public function comments(): HasMany
    {
        return $this->hasMany(BookComment::class)->whereNull('parent_id')->with(['user', 'replies', 'likes'])->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BookComment::class);
    }

    public function averageRating(): float
    {
        $avg = $this->allComments()->whereNotNull('rating')->avg('rating');
        return $avg ? round((float) $avg, 1) : 0.0;
    }

    public function ratingsCount(): int
    {
        return $this->allComments()->whereNotNull('rating')->count();
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

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_saved_books')
                    ->withTimestamps();
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

