<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopupRequest extends Model
{
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PENDING_REVIEW   = 'pending_review';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_EXPIRED          = 'expired';

    /** Hali yakunlanmagan (summa band qilingan) holatlar */
    public const OPEN_STATUSES = [self::STATUS_AWAITING_PAYMENT, self::STATUS_PENDING_REVIEW];

    protected $fillable = [
        'user_id',
        'reference',
        'package_key',
        'package_title',
        'coins',
        'points',
        'base_amount',
        'expected_amount',
        'card_last4',
        'status',
        'expires_at',
        'receipt_path',
        'receipt_mime',
        'receipt_size',
        'receipt_sha256',
        'receipt_dhash',
        'payer_card_last4',
        'user_note',
        'submitted_at',
        'ai_status',
        'ai_result',
        'extracted_transaction_id',
        'extracted_amount',
        'checks',
        'risk_score',
        'reviewed_by',
        'reviewed_at',
        'reject_reason',
        'admin_note',
    ];

    protected $casts = [
        'coins'            => 'integer',
        'points'           => 'integer',
        'base_amount'      => 'integer',
        'expected_amount'  => 'integer',
        'receipt_size'     => 'integer',
        'extracted_amount' => 'integer',
        'risk_score'       => 'integer',
        'ai_result'        => 'array',
        'checks'           => 'array',
        'expires_at'       => 'datetime',
        'submitted_at'     => 'datetime',
        'reviewed_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === self::STATUS_AWAITING_PAYMENT;
    }

    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function isFinal(): bool
    {
        return !in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AWAITING_PAYMENT => 'To\'lov kutilmoqda',
            self::STATUS_PENDING_REVIEW   => 'Tekshirilmoqda',
            self::STATUS_APPROVED         => 'Tasdiqlandi',
            self::STATUS_REJECTED         => 'Rad etildi',
            self::STATUS_CANCELLED        => 'Bekor qilindi',
            self::STATUS_EXPIRED          => 'Muddati o\'tdi',
            default                       => $this->status,
        };
    }

    /** Xavf darajasi: low / medium / high */
    public function getRiskLevelAttribute(): string
    {
        return $this->risk_score >= 60 ? 'high' : ($this->risk_score >= 25 ? 'medium' : 'low');
    }

    public function getRewardLabelAttribute(): string
    {
        $parts = [];
        if ($this->coins > 0) {
            $parts[] = number_format($this->coins, 0, '.', ' ') . ' tanga';
        }
        if ($this->points > 0) {
            $parts[] = number_format($this->points, 0, '.', ' ') . ' ball';
        }

        return implode(' + ', $parts);
    }
}
