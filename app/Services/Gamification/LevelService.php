<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Services\NotifyUser;

class LevelService
{
    /**
     * Darajalar konfiguratsiyasi:
     * [daraja_raqami => [threshold, title, icon, coin_bonus]]
     */
    public static function levels(): array
    {
        return [
            1 => ['threshold' => 0,      'title' => "Boshlang'ich Kitobxon", 'icon' => '🌱', 'coin_bonus' => 0],
            2 => ['threshold' => 500,    'title' => "Faol O'quvchi",          'icon' => '📖', 'coin_bonus' => 30],
            3 => ['threshold' => 1500,   'title' => 'Kitob Shinavandasi',     'icon' => '🕯️', 'coin_bonus' => 50],
            4 => ['threshold' => 3500,   'title' => "Zukko Qalb",             'icon' => '💡', 'coin_bonus' => 80],
            5 => ['threshold' => 7000,   'title' => 'Fikr Allomasi',          'icon' => '📜', 'coin_bonus' => 120],
            6 => ['threshold' => 12000,  'title' => 'Akademik Donishmand',    'icon' => '🏛️', 'coin_bonus' => 200],
            7 => ['threshold' => 20000,  'title' => 'Afsonaviy Mutafakkir',   'icon' => '👑', 'coin_bonus' => 350],
        ];
    }

    /**
     * Berilgan ball miqdori uchun daraja raqamini qaytaradi (1–7)
     */
    public static function getLevelForPoints(int $points): int
    {
        $levels = self::levels();
        $currentLevel = 1;

        foreach ($levels as $level => $data) {
            if ($points >= $data['threshold']) {
                $currentLevel = $level;
            }
        }

        return $currentLevel;
    }

    /**
     * Daraja ma'lumotlarini qaytaradi
     */
    public static function getLevelInfo(int $level): array
    {
        $levels = self::levels();
        return $levels[$level] ?? $levels[1];
    }

    /**
     * Foydalanuvchi hozirgi darajasi uchun keyingi daraja progress (0.0–1.0)
     */
    public static function getLevelProgress(int $points): array
    {
        $currentLevel = self::getLevelForPoints($points);
        $levels = self::levels();
        $currentInfo = $levels[$currentLevel];

        if ($currentLevel >= 7) {
            return [
                'current_level'     => $currentLevel,
                'current_info'      => $currentInfo,
                'next_level'        => null,
                'next_info'         => null,
                'progress_percent'  => 100,
                'points_needed'     => 0,
                'points_in_level'   => $points - $currentInfo['threshold'],
                'level_total_range' => 0,
            ];
        }

        $nextLevel = $currentLevel + 1;
        $nextInfo = $levels[$nextLevel];

        $levelRangeStart = $currentInfo['threshold'];
        $levelRangeEnd   = $nextInfo['threshold'];
        $levelRange      = $levelRangeEnd - $levelRangeStart;
        $pointsInLevel   = $points - $levelRangeStart;
        $percent         = $levelRange > 0 ? min(100, (int) round(($pointsInLevel / $levelRange) * 100)) : 100;

        return [
            'current_level'     => $currentLevel,
            'current_info'      => $currentInfo,
            'next_level'        => $nextLevel,
            'next_info'         => $nextInfo,
            'progress_percent'  => $percent,
            'points_needed'     => max(0, $levelRangeEnd - $points),
            'points_in_level'   => $pointsInLevel,
            'level_total_range' => $levelRange,
        ];
    }

    /**
     * Ball berilgandan keyin level-up bo'lganini tekshiradi va bonus coin beradi.
     * PointsService::awardPoints() dan keyin chaqiriladi.
     */
    public function checkAndAwardLevelUp(User $user, int $oldPoints, int $newPoints): ?array
    {
        $oldLevel = self::getLevelForPoints($oldPoints);
        $newLevel = self::getLevelForPoints($newPoints);

        if ($newLevel <= $oldLevel) {
            return null; // Daraja ko'tarilmadi
        }

        // Yangi daraja info
        $levelInfo = self::getLevelInfo($newLevel);
        $coinBonus = $levelInfo['coin_bonus'];

        // Coin bonus berish
        if ($coinBonus > 0) {
            $pointsService = app(PointsService::class);
            $user->refresh();
            $pointsService->awardCoins(
                $user,
                $coinBonus,
                'level_up_bonus',
                "Daraja ko'tarildi: {$levelInfo['icon']} {$levelInfo['title']} (Lv.{$newLevel}) — bonus {$coinBonus} tanga"
            );
        }

        // Bildirishnoma yuborish
        NotifyUser::send(
            $user,
            'level_up',
            "🎉 Yangi daraja! {$levelInfo['icon']} {$levelInfo['title']}",
            "Tabriklaymiz! Siz Lv.{$newLevel} — \"{$levelInfo['title']}\" darajasiga ko'tarildingiz!" .
                ($coinBonus > 0 ? " Bonus: +{$coinBonus} tanga!" : ''),
            $levelInfo['icon'],
            '/profile'
        );

        return [
            'old_level'   => $oldLevel,
            'new_level'   => $newLevel,
            'level_info'  => $levelInfo,
            'coin_bonus'  => $coinBonus,
        ];
    }
}
