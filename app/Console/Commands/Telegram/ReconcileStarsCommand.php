<?php

namespace App\Console\Commands\Telegram;

use App\Services\Telegram\TelegramPaymentService;
use Illuminate\Console\Command;

class ReconcileStarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:reconcile-stars {--limit=100 : Tekshiriladigan oxirgi tranzaksiyalar soni}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'getStarTransactions orqali to\'langan, ammo berilmay qolgan (pending) Telegram Stars to\'lovlarini solishtirish va tiklash';

    public function handle(TelegramPaymentService $paymentService): int
    {
        if (empty(config('telegram.bot_token'))) {
            $this->warn('TELEGRAM_BOT_TOKEN sozlanmagan. Solishtirish o\'tkazib yuborildi.');
            return self::SUCCESS;
        }

        $limit = max(1, min(100, (int) $this->option('limit')));
        $result = $paymentService->reconcileStarTransactions($limit);

        if (!$result['success']) {
            $this->error('Telegram Stars tranzaksiyalarini olishda xatolik: ' . ($result['message'] ?? 'Noma\'lum xato'));
            return self::FAILURE;
        }

        $this->info("Tekshirildi: {$result['checked']} ta tranzaksiya. Tiklandi: {$result['fulfilled']} ta to'lov.");
        return self::SUCCESS;
    }
}
