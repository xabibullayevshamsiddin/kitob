<?php

namespace App\Console\Commands\Telegram;

use App\Models\TelegramUpdate;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class PollTelegramUpdatesCommand extends Command
{
    protected $signature = 'telegram:poll 
                            {--once : Faqat mavjud yangilanishlarni qayta ishlab to\'xtash} 
                            {--timeout=20 : Long polling kutish soniyasi}';

    protected $description = 'Telegram bot yangilanishlarini lokal muhitda tinglash (Polling rejimi)';

    public function handle(): int
    {
        $botUsername = config('telegram.bot_username');
        $this->info("Telegram bot polling rejimi ishga tushmoqda (@{$botUsername})...");

        try {
            $client = new TelegramClient();
            $botService = new TelegramBotService($client);

            // Webhook mavjud bo'lsa tekshirish (xatolik bo'lsa ham polling to'xtab qolmasligi uchun xavfsiz)
            try {
                $info = $client->getWebhookInfo();
                if (!empty($info['result']['url'])) {
                    $this->warn("Webhook o'rnatilgan: " . $info['result']['url']);
                    $this->line("Lokal muhitda xabarlarni to'g'ridan-to'g'ri qabul qilish uchun webhook tozalanmoqda...");
                    $client->deleteWebhook();
                    $this->info("Webhook olib tashlandi. Endi xabarlar to'g'ridan-to'g'ri olinadi.");
                }
            } catch (\Throwable $wException) {
                $this->warn("Webhook tekshirishda ogohlantirish: " . $wException->getMessage());
            }

            $offset = 0;
            $this->info("Bot xabarlarni tinglamoqda... (To'xtatish uchun: Ctrl + C)");

            do {
                try {
                    $response = $client->getUpdates([
                        'offset'  => $offset,
                        'timeout' => (int) $this->option('timeout'),
                    ]);

                    if (($response['ok'] ?? false) && !empty($response['result'])) {
                        foreach ($response['result'] as $update) {
                            $updateId = (int) $update['update_id'];
                            $offset = $updateId + 1;

                            $sender = $update['message']['from']['first_name'] ?? ($update['callback_query']['from']['first_name'] ?? 'Foydalanuvchi');
                            $text = $update['message']['text'] ?? ($update['callback_query']['data'] ?? '[media/boshqa]');

                            $this->line("<fg=green>[Update #{$updateId}]</> <fg=cyan>{$sender}:</> {$text}");

                            // De-duplication
                            if (!TelegramUpdate::hasBeenProcessed($updateId)) {
                                TelegramUpdate::recordIfNew($updateId);
                                $botService->handleUpdate($update);
                                $this->info("  -> Javob yuborildi!");
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->error("So'rovda xato: " . $e->getMessage());
                    sleep(2);
                }

                if ($this->option('once')) {
                    break;
                }
            } while (true);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Kritik xatolik: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
