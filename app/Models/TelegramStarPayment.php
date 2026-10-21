<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramStarPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payload',
        'title',
        'stars_amount',
        'coins_awarded',
        'telegram_payment_charge_id',
        'provider_payment_charge_id',
        'status',
        'refunded_at',
        'raw_payload',
    ];

    protected $casts = [
        'stars_amount'  => 'integer',
        'coins_awarded' => 'integer',
        'refunded_at'   => 'datetime',
        'raw_payload'   => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
