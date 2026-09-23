<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'quote_text',
        'send_date',
    ];

    protected $casts = [
        'send_date' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function userQuotes(): HasMany
    {
        return $this->hasMany(UserQuote::class, 'quote_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('send_date', today());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('send_date', '>', today())->orderBy('send_date');
    }
}
