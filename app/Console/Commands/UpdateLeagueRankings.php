<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLeagueRankings extends Command
{
    protected $signature = 'leagues:update-rankings';

    protected $description = 'Haftalik ligalar (Bronza, Kumush, Oltin) o\'rtasida ko\'tarilish va tushishni hisoblash (Yakshanbalik hisob)';

    public function handle(): int
    {
        $this->info("Haftalik liga natijalari hisoblanmoqda...");

        $now = Carbon::now('Asia/Tashkent');
        $weekStart = Carbon::now('Asia/Tashkent')->startOfWeek();

        // 1. Har bir foydalanuvchining joriy haftadagi ballarini hisoblash
        $users = User::query()
            ->withSum(['pointTransactions as week_pts' => function ($q) use ($weekStart) {
                $q->where('created_at', '>=', $weekStart);
            }], 'points')
            ->get();

        foreach ($users as $user) {
            $pts = (int) ($user->week_pts ?? 0);
            $user->update([
                'weekly_points' => $pts,
                'league_updated_at' => $now->toDateString(),
            ]);
        }

        // 2. Oltin liga: eng past 3 ta kumushga tushadi
        $goldUsers = User::where('league', 'gold')->orderByDesc('weekly_points')->get();
        if ($goldUsers->count() > 5) {
            $relegateToSilver = $goldUsers->slice(-3);
            foreach ($relegateToSilver as $u) {
                $u->update(['league' => 'silver']);
                $this->line("Oltindan Kumushga tushdi: {$u->name}");
            }
        }

        // 3. Kumush liga: eng yaxshi 5 ta oltinga chiqadi, eng past 3 ta bronzaga tushadi
        $silverUsers = User::where('league', 'silver')->orderByDesc('weekly_points')->get();
        if ($silverUsers->count() > 5) {
            $promoteToGold = $silverUsers->take(5);
            foreach ($promoteToGold as $u) {
                $u->update(['league' => 'gold']);
                $this->line("Kumushdan Oltinga ko'tarildi: {$u->name}");
            }

            if ($silverUsers->count() > 8) {
                $relegateToBronze = $silverUsers->slice(-3);
                foreach ($relegateToBronze as $u) {
                    $u->update(['league' => 'bronze']);
                    $this->line("Kumushdan Bronzaga tushdi: {$u->name}");
                }
            }
        }

        // 4. Bronza liga: eng yaxshi 5 ta kumushga chiqadi
        $bronzeUsers = User::where('league', 'bronze')->orderByDesc('weekly_points')->get();
        if ($bronzeUsers->count() > 5) {
            $promoteToSilver = $bronzeUsers->take(5);
            foreach ($promoteToSilver as $u) {
                $u->update(['league' => 'silver']);
                $this->line("Bronzadan Kumushga ko'tarildi: {$u->name}");
            }
        }

        $this->info("Haftalik liga natijalari muvaffaqiyatli yangilandi.");
        return Command::SUCCESS;
    }
}
