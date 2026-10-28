<?php

namespace App\Services\Gamification;

use App\Models\PointTransaction;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserStreak;
use App\Services\NotifyUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MysteryChestService
{
    public const CHEST_COST_POINTS = 450;
    public const DAILY_LIMIT = 3;

    /**
     * Foydalanuvchi bugun ochgan sandiqlar sonini hisoblash (Toshkent vaqti bilan)
     */
    public static function getTodayOpenedCount(int $userId): int
    {
        $startOfDay = Carbon::now('Asia/Tashkent')->startOfDay()->utc();

        return (int) PointTransaction::where('user_id', $userId)
            ->where('source', 'mystery_chest')
            ->where('points', '<', 0)
            ->where('created_at', '>=', $startOfDay)
            ->count();
    }

    /**
     * Foydalanuvchi uchun bugungi qolgan sandiqlar soni
     */
    public static function getDailyRemaining(int $userId): int
    {
        return max(0, self::DAILY_LIMIT - self::getTodayOpenedCount($userId));
    }

    /**
     * Mukofotlar havzasi va ularning og'irliqlari (weight)
     * Jami weight = 1000, birlik = 0.1%
     */
    private static function rewardPool(): array
    {
        return [
            // [type, value, label, icon, weight]
            ['type' => 'coins', 'value' => 15,  'label' => '15 tanga',          'icon' => '🪙', 'weight' => 260],
            ['type' => 'coins', 'value' => 30,  'label' => '30 tanga',          'icon' => '🪙', 'weight' => 220],
            ['type' => 'coins', 'value' => 50,  'label' => '50 tanga',          'icon' => '💰', 'weight' => 180],
            ['type' => 'coins', 'value' => 80,  'label' => '80 tanga',          'icon' => '💰', 'weight' => 120],
            ['type' => 'coins', 'value' => 150, 'label' => '150 tanga (Katta!)', 'icon' => '💎', 'weight' => 40],
            ['type' => 'item',  'value' => 'streak_freeze',   'label' => 'Streak Muzlatgich (×1)', 'icon' => '❄️', 'weight' => 80],
            ['type' => 'item',  'value' => 'coin_booster_60', 'label' => 'Tanga Kuchaytirgich (×1)', 'icon' => '⚡', 'weight' => 60],
            ['type' => 'points','value' => 150, 'label' => '+150 ball qoʻshimcha', 'icon' => '⭐', 'weight' => 40],
        ];
    }

    /**
     * Mystery Chest ochish.
     * 450 ball sarflab tasodifiy mukofot olish (kuniga max 3 marta).
     * Muvaffaqiyatli bo'lsa reward ma'lumotlarini qaytaradi.
     *
     * @throws \RuntimeException
     */
    public function open(User $user): array
    {
        $cost = self::CHEST_COST_POINTS;

        // 1. Kunlik limit tekshiruvi (max 3 ta)
        $openedToday = self::getTodayOpenedCount($user->id);
        if ($openedToday >= self::DAILY_LIMIT) {
            throw new \RuntimeException("Kunlik limitga yetdingiz! Kuniga ko'pi bilan " . self::DAILY_LIMIT . " ta sandiq ochishingiz mumkin. Ertaga yana urinib ko'ring.");
        }

        // 2. Ball tekshiruvi
        if ((int) $user->total_points < $cost) {
            throw new \RuntimeException("Yetarli ball yo'q. Sandiq ochish uchun {$cost} ball kerak.");
        }

        // 3. Ball yechib olish
        $deducted = DB::transaction(function () use ($user, $cost) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$lockedUser || $lockedUser->total_points < $cost) {
                return false;
            }
            $lockedUser->decrement('total_points', $cost);
            $lockedUser->increment('points_spent', $cost);

            PointTransaction::create([
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

        // 4. Tasodifiy mukofot tanlash (weighted random)
        $reward = $this->pickReward();

        // 5. Mukofotni berish
        try {
            $this->grantReward($user, $reward);
        } catch (\Throwable $e) {
            report($e);
            // Agar biror item berishda xatolik bo'lsa, foydalanuvchi pulsiz qolmasligi uchun tanga beramiz
            app(PointsService::class)->awardCoins($user, 30, 'mystery_chest_fallback', 'Omad Sandig\'i mukofoti: 30 tanga');
            $reward = ['type' => 'coins', 'value' => 30, 'label' => '30 tanga', 'icon' => '🪙'];
        }

        // 6. Bildirishnoma
        try {
            NotifyUser::send(
                $user,
                'mystery_chest',
                "🎁 Omad Sandig'i ochildi!",
                "Siz sandiqdan {$reward['icon']} {$reward['label']} oldingiz! Baxtli bo'ling!",
                '🎁',
                '/shop?tab=chest'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $reward['remaining_today'] = self::getDailyRemaining($user->id);

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
            // Shop itemini `key` orqali topish (slug emas!)
            $item = ShopItem::where('key', $reward['value'])->first();
            if ($item) {
                $userItem = UserItem::firstOrNew([
                    'user_id'      => $user->id,
                    'shop_item_id' => $item->id,
                ]);
                $userItem->quantity = ($userItem->exists ? (int)$userItem->quantity : 0) + 1;
                $userItem->acquired_at = $userItem->acquired_at ?? now();
                $userItem->expires_at = now()->addDays(30);
                $userItem->save();

                // Agar Streak Freeze bo'lsa — user_streaks.freeze_count ni ham oshiramiz
                if ($item->key === 'streak_freeze') {
                    $streak = UserStreak::firstOrCreate(
                        ['user_id' => $user->id],
                        ['current_streak' => 0, 'longest_streak' => 0, 'freeze_count' => 0]
                    );
                    $streak->increment('freeze_count', 1);
                }
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
