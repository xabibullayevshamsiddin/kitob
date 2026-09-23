<?php

namespace App\Console\Commands;

use App\Services\Gamification\StreakService;
use Illuminate\Console\Command;

class CalculateDailyStreaks extends Command
{
    protected $signature = 'streaks:calculate';
    protected $description = 'Kunlik streaklarni tekshirish va kirmagan foydalanuvchilar streakini yangilash';

    public function handle(StreakService $streakService): int
    {
        $this->info('Streaklar hisob-kitobi boshlandi...');

        $resetCount = $streakService->processDailyResets();

        $this->info("Muvaffaqiyatli yakunlandi! {$resetCount} ta foydalanuvchi streaki yangilandi.");

        return Command::SUCCESS;
    }
}
