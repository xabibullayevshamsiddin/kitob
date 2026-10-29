<?php

namespace App\Services\Gamification;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    protected ?array $lastLevelUp = null;

    public function getLastLevelUp(): ?array
    {
        return $this->lastLevelUp;
    }

    /**
     * Foydalanuvchiga ball berish (Points) + level-up tekshiruvi
     */
    public function awardPoints(User $user, int $points, string $source, string $description, ?int $refId = null, ?string $refType = null): PointTransaction
    {
        $oldPoints = (int) $user->total_points;

        $transaction = DB::transaction(function () use ($user, $points, $source, $description, $refId, $refType) {
            $user->increment('total_points', $points);

            return PointTransaction::create([
                'user_id'        => $user->id,
                'points'         => $points,
                'source'         => $source,
                'description'    => $description,
                'reference_id'   => $refId,
                'reference_type' => $refType,
            ]);
        });

        // Level-up tekshiruvi (transaction tashqarisida, chunki awardCoins o'z transaksiyasida)
        $newPoints = (int) ($user->total_points ?? $oldPoints + $points);
        $this->lastLevelUp = app(LevelService::class)->checkAndAwardLevelUp($user, $oldPoints, $newPoints);

        return $transaction;
    }

    /**
     * Ball sarflash (Points spending) — Mystery Chest va boshqa points-based xaridlar uchun
     */
    public function spendPoints(User $user, int $points, string $description): bool
    {
        if ($points <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $points, $description) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$lockedUser || $lockedUser->total_points < $points) {
                return false;
            }

            $lockedUser->decrement('total_points', $points);

            // points_spent ni ham oshiramiz
            $lockedUser->increment('points_spent', $points);

            PointTransaction::create([
                'user_id'     => $lockedUser->id,
                'points'      => -$points,
                'source'      => 'spend',
                'description' => $description,
            ]);

            // user modelini yangilash
            $user->total_points  = $lockedUser->total_points;
            $user->points_spent  = $lockedUser->points_spent;

            return true;
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
