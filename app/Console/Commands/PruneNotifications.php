<?php

namespace App\Console\Commands;

use App\Services\NotifyUser;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune {--keep=20 : Har bir foydalanuvchida saqlanadigan maksimal bildirishnomalar soni}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Foydalanuvchilarning 20 tadan ortiq eski bildirishnomalarini avtomatik tozalash';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keep = (int) $this->option('keep') ?: NotifyUser::MAX_NOTIFICATIONS_PER_USER;

        $this->info("Bildirishnomalarni tozalash boshlandi (har bir foydalanuvchi uchun maks: {$keep} ta)...");

        $deleted = NotifyUser::pruneAllUsers($keep);

        $this->info("Muvaffaqiyatli yakunlandi! Jami {$deleted} ta eski bildirishnoma tozalandi.");

        return Command::SUCCESS;
    }
}
