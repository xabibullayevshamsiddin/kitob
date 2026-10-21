<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class GetWebhookInfoCommand extends Command
{
    protected $signature = 'telegram:webhook-info';

    protected $description = 'Telegram bot joriy webhook holatini tekshirish';

    public function handle(): int
    {
        $this->info("Telegram Webhook holati tekshirilmoqda...");

        try {
            $client = new TelegramClient();
            $response = $client->getWebhookInfo();

            if (($response['ok'] ?? false) !== true || !isset($response['result'])) {
                $this->error("Javob olinmadi: " . json_encode($response, JSON_UNESCAPED_UNICODE));
                return Command::FAILURE;
            }

            $info = $response['result'];

            $rows = [
                ['Webhook URL', $info['url'] ?: '(O\'rnatilmagan / polling rejimi)'],
                ['Maxsus sertifikat', !empty($info['has_custom_certificate']) ? 'Ha' : 'Yo\'q'],
                ['Kutilayotgan so\'rovlar soni', (string) ($info['pending_update_count'] ?? 0)],
                ['Maksimal ulanishlar', (string) ($info['max_connections'] ?? 'Standart')],
                ['IP manzil', (string) ($info['ip_address'] ?? 'Mavjud emas')],
                [
                    'Oxirgi xatolik sanasi',
                    !empty($info['last_error_date'])
                        ? date('Y-m-d H:i:s', $info['last_error_date'])
                        : '(Xatolik yo\'q)'
                ],
                [
                    'Oxirgi xatolik matni',
                    (string) ($info['last_error_message'] ?? '(Xatolik yo\'q)')
                ],
            ];

            $this->table(['Parametr', 'Qiymat'], $rows);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Xatolik: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
