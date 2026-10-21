<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class SetTelegramMenuButtonCommand extends Command
{
    protected $signature = 'telegram:set-menu-button 
                            {--url= : Telegram Mini App HTTPS URL manzili} 
                            {--default : Standart buyruqlar menyusiga qaytarish}';

    protected $description = 'Telegram botining pastki chap burchagidagi Menyu tugmasini sozlash (Mini App yoki standart)';

    public function handle(): int
    {
        $client = new TelegramClient();

        if ($this->option('default')) {
            $this->info("Telegram menyu tugmasi standart buyruqlar holatiga qaytarilmoqda...");
            $res = $client->setChatMenuButton(['type' => 'default']);
            $this->info("Menyu tugmasi standart buyruqlarga sozlandi: " . json_encode($res));
            return Command::SUCCESS;
        }

        $url = $this->option('url') ?: config('telegram.mini_app_url');

        if (empty($url) || !str_starts_with($url, 'https://')) {
            $this->error("Mini App URL manzili bo'sh yoki HTTPS bilan boshlanmagan: '{$url}'");
            $this->warn("Telegram Mini App ochilishi uchun URL majburiy ravishda HTTPS bo'lishi kerak.");
            return Command::FAILURE;
        }

        $this->info("Menyu tugmasi Mini App'ga sozlanmoqda: {$url}...");

        $menuButton = [
            'type'    => 'web_app',
            'text'    => '📖 Mutolaa',
            'web_app' => [
                'url' => $url,
            ],
        ];

        try {
            $res = $client->setChatMenuButton($menuButton);
            $this->info("Menyu tugmasi muvaffaqiyatli sozlandi! ✅");
            $this->line("Telegram chatida pastki chap burchakdagi tugma endi «📖 Mutolaa» deb chiqadi va to'g'ridan-to'g'ri Mini App'ni ochadi.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Xatolik yuz berdi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
