<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * Foydalanuvchining umumiy (all_time, points) bo'yicha reytingdagi o'rni.
     * Agar $user->total_points === 0 bo'lsa, null qaytadi.
     */
    public function getUserRank(User $user): ?int
    {
        if ((int) $user->total_points <= 0) {
            return null;
        }

        // $user'dan ko'proq ballga ega foydalanuvchilar soni + 1
        return User::where('total_points', '>', $user->total_points)->count() + 1;
    }

    /**
     * Top 5 foydalanuvchi ID'lari: [user_id => rank] shaklida.
     * Takroriy so'rovlarni kamaytirish uchun 120 soniyaga keshlangan.
     *
     * @return array<int, int> [user_id => rank (1..5)]
     */
    public function getTopFiveIds(): array
    {
        return Cache::remember('leaderboard_top_five_ids', 120, function () {
            $topUsers = User::where('total_points', '>', 0)
                ->orderByDesc('total_points')
                ->orderBy('id')
                ->take(5)
                ->get(['id', 'total_points']);

            $map = [];
            foreach ($topUsers as $index => $u) {
                $map[$u->id] = $index + 1;
            }

            return $map;
        });
    }

    /**
     * Top foydalanuvchilar to'plami (Hall of Fame vitrinasi uchun).
     *
     * @param int $limit
     * @return Collection<int, User>
     */
    public function getTopUsers(int $limit = 5): Collection
    {
        return Cache::remember("leaderboard_top_users_{$limit}", 120, function () use ($limit) {
            return User::where('total_points', '>', 0)
                ->with(['profile', 'streak'])
                ->orderByDesc('total_points')
                ->orderBy('id')
                ->take($limit)
                ->get()
                ->map(function ($u, $idx) {
                    $u->leaderboard_rank = $idx + 1;
                    return $u;
                });
        });
    }

    /**
     * Rank bo'yicha daraja (tier) nomi:
     * 1 => 'gold' (Oltin / Crown)
     * 2 => 'silver' (Kumush)
     * 3 => 'bronze' (Bronza)
     * 4, 5 => 'top5' (Top 5 nishoni)
     * > 5 => null
     */
    public function getRankTier(?int $rank): ?string
    {
        return match (true) {
            $rank === 1 => 'gold',
            $rank === 2 => 'silver',
            $rank === 3 => 'bronze',
            $rank === 4 || $rank === 5 => 'top5',
            default => null,
        };
    }
}
