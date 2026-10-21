<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class SetCommandsCommand extends Command
{
    protected $signature = 'telegram:set-commands';

    protected $description = 'Telegram bot menyusi buyruqlarini (commands) sozlash';

    public function handle(): int
    {
        $this->info("Telegram bot buyruqlari sozlanmoqda...");

        $commands = [
            ['command' => 'start', 'description' => 'Botni ishga tushirish va menyu 🚀'],
            ['command' => 'profil', 'description' => 'Shaxsiy profil va yutuqlar 👤'],
            ['command' => 'streak', 'description' => 'Kunlik mutolaa zanjiri (Streak) 🔥'],
            ['command' => 'balance', 'description' => 'Tanga va ballar balansi 🪙'],
            ['command' => 'hikmat', 'description' => 'Kun hikmati ✨'],
            ['command' => 'shop', 'description' => 'Platforma do\'koni 🛍'],
            ['command' => 'help', 'description' => 'Yordam va qo\'llanma ❓'],
            ['command' => 'stop', 'description' => 'Bildirishnomalarni to\'xtatish 🔕'],
        ];

        try {
            $client = new TelegramClient();
            $res = $client->setMyCommands($commands);

            if (($res['ok'] ?? false) === true) {
                $this->info("Muvaffaqiyatli! Barcha buyruqlar bot menyusiga o'rnatildi.");
                $this->table(['Buyruq', 'Tavsif'], $commands);
                return Command::SUCCESS;
            }

            $this->error("Xatolik: " . json_encode($res, JSON_UNESCAPED_UNICODE));
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Xatolik: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
