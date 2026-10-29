<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ProcessExpiredBansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:process-expired-bans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muddati tugagan vaqtinchalik banlarni bekor qilish va Telegram xabarlarini yuborish';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Muddati o\'tgan banlar tekshirilmoqda...');

        $unbanned = User::processExpiredBans();

        if ($unbanned > 0) {
            $this->info("✅ {$unbanned} ta foydalanuvchi blokdan chiqarildi va bildirishnomalar yuborildi.");
        } else {
            $this->line('ℹ️ Hozirda muddati o\'tgan banlar mavjud emas.');
        }

        return self::SUCCESS;
    }
}
