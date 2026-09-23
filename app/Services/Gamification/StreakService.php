<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStreak;
use Carbon\Carbon;

class StreakService
{
    protected string $timezone = 'Asia/Tashkent';

    /**
     * Foydalanuvchi faolligini qayd etish va streakni oshirish
     */
    public function recordActivity(User $user): UserStreak
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => null]
        );

        $today = Carbon::now($this->timezone)->toDateString();
        $yesterday = Carbon::yesterday($this->timezone)->toDateString();

        // 1. Agar bugun allaqachon faollik hisoblangan bo'lsa, hech narsa qilmaymiz
        if ($streak->last_active_date && $streak->last_active_date->toDateString() === $today) {
            return $streak;
        }

        // 2. Agar kecha kirgan bo'lsa, streak + 1
        if ($streak->last_active_date && $streak->last_active_date->toDateString() === $yesterday) {
            $streak->current_streak += 1;
        } else {
            // Bir kun uzilib qolgan bo'lsa, qaytadan 1 dan boshlanadi
            $streak->current_streak = 1;
        }

        // 3. Tarixiy rekord (longest streak) ni tekshirish
        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_active_date = $today;
        $streak->save();

        // 4. Streak bo'yicha Badge (yutuqlar) ni tekshirish
        $this->checkStreakBadges($user, $streak->current_streak);

        return $streak;
    }

    /**
     * Tungi hisob-kitob: agar kecha kirmagan bo'lsa, current_streak = 0 ga tushirish
     */
    public function processDailyResets(): int
    {
        $yesterday = Carbon::yesterday($this->timezone)->toDateString();
        $today = Carbon::today($this->timezone)->toDateString();

        // Oxirgi faol kuni kecha ham, bugun ham bo'lmagan foydalanuvchilar streakini 0 ga tushiramiz
        return UserStreak::where('current_streak', '>', 0)
            ->where(function ($q) use ($yesterday, $today) {
                $q->whereNull('last_active_date')
                  ->orWhere(function ($sub) use ($yesterday, $today) {
                      $sub->whereDate('last_active_date', '<', $yesterday);
                  });
            })
            ->update(['current_streak' => 0]);
    }

    protected function checkStreakBadges(User $user, int $currentStreak): void
    {
        $badge = Badge::where('condition_type', 'streak_days')
            ->where('condition_value', '<=', $currentStreak)
            ->orderBy('condition_value', 'desc')
            ->first();

        if ($badge) {
            UserBadge::firstOrCreate([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
            ], [
                'earned_at' => now(),
            ]);
        }
    }
}
