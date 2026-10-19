<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coins',
        'source',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
    ];

    protected $casts = [
        'coins'         => 'integer',
        'reference_id'  => 'integer',
        'balance_after' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('coins', '>', 0);
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('coins', '<', 0);
    }
}
