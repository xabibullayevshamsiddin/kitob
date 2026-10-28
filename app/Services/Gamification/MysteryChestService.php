<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\UserItem;
use App\Models\ShopItem;
use App\Services\NotifyUser;
use Illuminate\Support\Facades\DB;

class MysteryChestService
{
    public const CHEST_COST_POINTS = 300;

    /**
     * Mukofotlar havzasi va ularning og'irliqlari (weight)
     * Jami weight = 1000, birlik = 0.1%
     */
    private static function rewardPool(): array
    {
        return [
            // [type, value, label, icon, weight]
            ['type' => 'coins', 'value' => 10,  'label' => '10 tanga',          'icon' => '🪙', 'weight' => 280],
            ['type' => 'coins', 'value' => 20,  'label' => '20 tanga',          'icon' => '🪙', 'weight' => 220],
            ['type' => 'coins', 'value' => 30,  'label' => '30 tanga',          'icon' => '🪙', 'weight' => 160],
            ['type' => 'coins', 'value' => 50,  'label' => '50 tanga',          'icon' => '💰', 'weight' => 120],
            ['type' => 'coins', 'value' => 80,  'label' => '80 tanga',          'icon' => '💰', 'weight' => 70],
            ['type' => 'coins', 'value' => 120, 'label' => '120 tanga (Katta!)', 'icon' => '💎', 'weight' => 30],
            ['type' => 'item',  'value' => 'streak_freeze',   'label' => 'Streak Muzlatgich (×1)', 'icon' => '❄️', 'weight' => 70],
            ['type' => 'item',  'value' => 'coin_booster_60', 'label' => 'Tanga Kuchaytirgich (×1)', 'icon' => '⚡', 'weight' => 40],
            ['type' => 'points','value' => 100, 'label' => '+100 ball qoʻshimcha', 'icon' => '⭐', 'weight' => 10],
        ];
    }

    /**
     * Mystery Chest ochish.
     * 300 ball sarflab tasodifiy mukofot olish.
     * Muvaffaqiyatli bo'lsa reward ma'lumotlarini qaytaradi.
     * Xato bo'lsa istisno tashlaydi.
     *
     * @throws \RuntimeException
     */
    public function open(User $user): array
    {
        $cost = self::CHEST_COST_POINTS;

        // 1. Ball tekshiruvi
        if ((int) $user->total_points < $cost) {
            throw new \RuntimeException("Yetarli ball yo'q. Sandiq ochish uchun {$cost} ball kerak.");
        }

        // 2. Ball yechib olish
        $pointsService = app(PointsService::class);
        $deducted = DB::transaction(function () use ($user, $cost) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$lockedUser || $lockedUser->total_points < $cost) {
                return false;
            }
            $lockedUser->decrement('total_points', $cost);
            $lockedUser->increment('points_spent', $cost);

            \App\Models\PointTransaction::create([
                'user_id'     => $lockedUser->id,
                'points'      => -$cost,
                'source'      => 'mystery_chest',
                'description' => "Omad Sandig'i ochildi (-{$cost} ball)",
            ]);

            // User modelini yangilash
            $user->total_points = $lockedUser->total_points;
            $user->points_spent = $lockedUser->points_spent;

            return true;
        });

        if (!$deducted) {
            throw new \RuntimeException("Yetarli ball yo'q. Sandiq ochish uchun {$cost} ball kerak.");
        }

        // 3. Tasodifiy mukofot tanlash (weighted random)
        $reward = $this->pickReward();

        // 4. Mukofotni berish
        $this->grantReward($user, $reward);

        // 5. Bildirishnoma
        NotifyUser::send(
            $user,
            'mystery_chest',
            "🎁 Omad Sandig'i ochildi!",
            "Siz sandiqdan {$reward['icon']} {$reward['label']} oldingiz! Baxtli bo'ling!",
            '🎁',
            '/shop'
        );

        return $reward;
    }

    /**
     * Weighted random tanlash
     */
    private function pickReward(): array
    {
        $pool = self::rewardPool();
        $totalWeight = array_sum(array_column($pool, 'weight'));
        $rand = random_int(1, $totalWeight);

        $cumulative = 0;
        foreach ($pool as $reward) {
            $cumulative += $reward['weight'];
            if ($rand <= $cumulative) {
                return $reward;
            }
        }

        // Fallback: birinchi element
        return $pool[0];
    }

    /**
     * Mukofotni foydalanuvchiga berish
     */
    private function grantReward(User $user, array $reward): void
    {
        $user->refresh();
        $pointsService = app(PointsService::class);

        if ($reward['type'] === 'coins') {
            $pointsService->awardCoins(
                $user,
                (int) $reward['value'],
                'mystery_chest_reward',
                "Omad Sandig'i mukofoti: {$reward['label']}"
            );
        } elseif ($reward['type'] === 'item') {
            // Shop itemini foydalanuvchi inventariga qo'shish
            $item = ShopItem::where('slug', $reward['value'])->first();
            if ($item) {
                UserItem::create([
                    'user_id'    => $user->id,
                    'item_id'    => $item->id,
                    'quantity'   => 1,
                    'expires_at' => now()->addDays(30),
                ]);
            }
        } elseif ($reward['type'] === 'points') {
            // Points qayta berish (level-up ham triggerlanadi)
            $pointsService->awardPoints(
                $user,
                (int) $reward['value'],
                'mystery_chest_reward',
                "Omad Sandig'i mukofoti: {$reward['label']}"
            );
        }
    }

    /**
     * Sandiq ochish narxi
     */
    public static function getCost(): int
    {
        return self::CHEST_COST_POINTS;
    }
}
