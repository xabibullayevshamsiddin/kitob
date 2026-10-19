<?php

namespace App\Services\Gamification;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Foydalanuvchiga ball berish (Points)
     */
    public function awardPoints(User $user, int $points, string $source, string $description, ?int $refId = null, ?string $refType = null): PointTransaction
    {
        return DB::transaction(function () use ($user, $points, $source, $description, $refId, $refType) {
            $user->increment('total_points', $points);

            return PointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
                'description' => $description,
                'reference_id' => $refId,
                'reference_type' => $refType,
            ]);
        });
    }

    /**
     * Foydalanuvchiga tanga berish (Coins)
     */
    public function awardCoins(User $user, int $coins, string $source, string $description): CoinTransaction
    {
        return DB::transaction(function () use ($user, $coins, $source, $description) {
            $user->increment('coin_balance', $coins);

            return CoinTransaction::create([
                'user_id' => $user->id,
                'coins' => $coins,
                'source' => $source,
                'description' => $description,
            ]);
        });
    }

    /**
     * Tanga sarflash (Coins spending)
     *
     * @deprecated Yangi do'kon va xaridlar uchun \App\Services\Shop\ShopService::purchase() ishlatilsin.
     */
    public function spendCoins(User $user, int $coins, string $description): bool
    {
        if ($coins <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $coins, $description) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$lockedUser || $lockedUser->coin_balance < $coins) {
                return false;
            }

            $lockedUser->decrement('coin_balance', $coins);

            CoinTransaction::create([
                'user_id'       => $lockedUser->id,
                'coins'         => -$coins,
                'source'        => 'spend',
                'description'   => $description,
                'balance_after' => $lockedUser->coin_balance,
            ]);

            return true;
        });
    }
}
