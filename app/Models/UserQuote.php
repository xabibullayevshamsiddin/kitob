<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quote_id',
        'liked',
        'saved',
    ];

    protected $casts = [
        'liked' => 'boolean',
        'saved' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(DailyQuote::class, 'quote_id');
    }
}
