<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'category',
        'slot',
        'name',
        'description',
        'price_coins',
        'rarity',
        'payload',
        'is_consumable',
        'max_quantity',
        'duration_minutes',
        'is_active',
        'available_from',
        'available_until',
        'sort_order',
    ];

    protected $casts = [
        'name'             => 'array',
        'description'      => 'array',
        'payload'          => 'array',
        'price_coins'      => 'integer',
        'is_consumable'    => 'boolean',
        'max_quantity'     => 'integer',
        'duration_minutes' => 'integer',
        'is_active'        => 'boolean',
        'available_from'   => 'datetime',
        'available_until'  => 'datetime',
        'sort_order'       => 'integer',
    ];

    /**
     * Joriy tilga mos nomni olish (fallback: uz -> ru -> en).
     */
    public function getNameTextAttribute(): string
    {
        $names = is_array($this->name) ? $this->name : [];
        $locale = app()->getLocale();

        return (string) ($names[$locale] ?? $names['uz'] ?? $names['ru'] ?? $names['en'] ?? $this->key);
    }

    /**
     * Joriy tilga mos tavsifni olish.
     */
    public function getDescriptionTextAttribute(): string
    {
        $desc = is_array($this->description) ? $this->description : [];
        $locale = app()->getLocale();

        return (string) ($desc[$locale] ?? $desc['uz'] ?? $desc['ru'] ?? $desc['en'] ?? '');
    }

    /**
     * Til bo'yicha tarjimani olish (uz, ru, en).
     */
    public function getTranslation(string $field, ?string $locale = null): string
    {
        $arr = is_array($this->{$field}) ? $this->{$field} : [];
        $loc = $locale ?: app()->getLocale();

        return (string) ($arr[$loc] ?? $arr['uz'] ?? $arr['ru'] ?? $arr['en'] ?? '');
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->name_text;
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->description_text;
    }

    public function getCategoryLabelAttribute(): string
    {
        $translated = trans('shop.categories.' . $this->category);
        return is_string($translated) && !str_starts_with($translated, 'shop.')
            ? $translated
            : ucfirst((string) $this->category);
    }

    public function getRarityLabelAttribute(): string
    {
        $t1 = trans('shop.rarity_' . $this->rarity);
        if (is_string($t1) && !str_starts_with($t1, 'shop.')) return $t1;

        $t2 = trans('shop.rarity.' . $this->rarity);
        if (is_string($t2) && !str_starts_with($t2, 'shop.')) return $t2;

        return ucfirst((string) $this->rarity);
    }

    public function getRarityColorAttribute(): string
    {
        return match ($this->rarity) {
            'legendary' => 'text-amber-400',
            'epic'      => 'text-purple-400',
            'rare'      => 'text-blue-400',
            default     => 'text-mist',
        };
    }

    /**
     * Faol va muddat oynasida ekanligini tekshirish scope'i.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
            });
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeBySlot(Builder $query, string $slot): Builder
    {
        return $query->where('slot', $slot);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Payload ichidagi rangni xavfsiz HEX formatida olish (CSS inyeksiyalariga qarshi himoya).
     */
    public function safeColor(string $key = 'color', string $fallback = '#F59E0B'): string
    {
        $payload = is_array($this->payload) ? $this->payload : [];
        $val = $payload[$key] ?? null;

        if (is_string($val) && \App\Services\Shop\ShopService::validateHexColor(trim($val))) {
            return trim($val);
        }

        return $fallback;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(UserEquipment::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ShopPurchase::class);
    }
}
