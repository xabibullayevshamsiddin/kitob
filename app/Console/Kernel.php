<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Kunlik streaklarni hisob-kitob qilish (Asia/Tashkent vaqti bilan har kecha 00:05 da)
        $schedule->command('streaks:calculate')
            ->dailyAt('00:05')
            ->timezone('Asia/Tashkent');

        // Kunlik analitika snapshotini yig'ish (Asia/Tashkent vaqti bilan har kecha 00:10 da)
        $schedule->command('analytics:snapshot')
            ->dailyAt('00:10')
            ->timezone('Asia/Tashkent');

        // Foydalanuvchilarning 20 tadan ortiq eski bildirishnomalarini tungi avtomatik tozalash
        $schedule->command('notifications:prune')
            ->dailyAt('03:00')
            ->timezone('Asia/Tashkent');

        // Kunlik Telegram kun hikmati (har kuni ertalab 09:00 da)
        $schedule->command('telegram:send-daily-quote')
            ->dailyAt('09:00')
            ->timezone('Asia/Tashkent');

        // Kunlik Streak buzilish xavfi eslatmasi (har kuni kechki 20:30 da)
        $schedule->command('telegram:send-streak-reminders')
            ->dailyAt('20:30')
            ->timezone('Asia/Tashkent');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
