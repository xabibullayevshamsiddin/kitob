<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class SetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook 
                            {--url= : Maxsus webhook URL (standart: APP_URL/telegram/webhook)} 
                            {--drop-pending-updates : Kutilayotgan eski so\'rovlarni tozalash}';

    protected $description = 'Telegram bot webhook manzilini va maxfiy tokenni o\'rnatish';

    public function handle(): int
    {
        $url = $this->option('url') ?: rtrim(config('app.url'), '/') . '/telegram/webhook';
        $dropPending = (bool) $this->option('drop-pending-updates');
        $secret = config('telegram.webhook_secret');

        if (empty($secret)) {
            $this->warn("DIQQAT: TELEGRAM_WEBHOOK_SECRET .env faylida belgilanmagan!");
        }

        $this->info("Webhook o'rnatilmoqda: {$url}");

        try {
            $client = new TelegramClient();
            $response = $client->setWebhook($url, [
                'drop_pending_updates' => $dropPending,
            ]);

            if (($response['ok'] ?? false) === true) {
                $this->info("Muvaffaqiyatli! Webhook o'rnatildi.");
                $this->line("Telegram javobi: " . ($response['description'] ?? 'OK'));
                return Command::SUCCESS;
            }

            $this->error("Telegram xatosi: " . json_encode($response, JSON_UNESCAPED_UNICODE));
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Xatolik: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
