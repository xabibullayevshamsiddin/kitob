<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPurchase extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'shop_item_id',
        'quantity',
        'price_paid',
        'coin_transaction_id',
        'purchase_token',
        'created_at',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'price_paid' => 'integer',
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class);
    }

    public function coinTransaction(): BelongsTo
    {
        return $this->belongsTo(CoinTransaction::class);
    }
}
