<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TelegramUpdate extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'telegram_updates';

    protected $primaryKey = 'update_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'update_id',
        'chat_id',
        'update_type',
        'created_at',
    ];

    protected $casts = [
        'update_id'  => 'integer',
        'chat_id'    => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Berilgan update_id avval qayta ishlanganligini tekshiradi.
     */
    public static function hasBeenProcessed(int|string $updateId): bool
    {
        return static::where('update_id', $updateId)->exists();
    }

    /**
     * Agar mavjud bo'lmasa, yangi update_id ni yozib qo'yadi.
     * Muvaffaqiyatli saqlansa true, avval mavjud bo'lgan bo'lsa false qaytaradi.
     */
    public static function recordIfNew(int|string $updateId, ?int $chatId = null, ?string $updateType = null): bool
    {
        try {
            static::create([
                'update_id'   => $updateId,
                'chat_id'     => $chatId,
                'update_type' => $updateType,
                'created_at'  => now(),
            ]);
            return true;
        } catch (\Throwable $e) {
            // Unique key collision yoki boshqa parallel so'rov
            return false;
        }
    }

    /**
     * 7 kundan (yoki berilgan kundan) eski yozuvlar uchun scope.
     */
    public function scopeOlderThan(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * 7 kundan eski yozuvlarni o'chirish.
     */
    public static function pruneOlderThan(int $days = 7): int
    {
        return static::olderThan($days)->delete();
    }
}
