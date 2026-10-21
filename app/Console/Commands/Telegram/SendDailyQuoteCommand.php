<?php

namespace App\Console\Commands\Telegram;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\DailyQuote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailyQuoteCommand extends Command
{
    protected $signature = 'telegram:send-daily-quote';

    protected $description = 'Foydalanuvchilarga kun hikmatini yuborish';

    public function handle(): int
    {
        $today = Carbon::now('Asia/Tashkent')->toDateString();

        $quote = DailyQuote::with('book')
            ->whereDate('send_date', '<=', $today)
            ->latest('send_date')
            ->first();

        if (!$quote) {
            $this->warn("Bugun uchun faol hikmat topilmadi.");
            return Command::SUCCESS;
        }

        $this->info("Kun hikmati tarqatilmoqda...");

        $author = $quote->book?->author ?? 'Muallif';
        $message = "✨ <b>Kun hikmati</b> 📚\n\n"
                 . "«{$quote->quote_text}»\n\n"
                 . "— <i>{$author}</i>";

        if ($quote->book) {
            $message .= "\n\n📖 <b>«{$quote->book->title}»</b> kitobidan";
        }

        $appUrl = config('app.url', 'https://kitobxon.laravel.cloud');
        $buttons = [];

        if ($quote->book) {
            $buttons[] = [
                ['text' => '📖 Kitobni mutolaa qilish', 'url' => "{$appUrl}/books/{$quote->book->slug}"]
            ];
        }

        $options = !empty($buttons) ? ['reply_markup' => ['inline_keyboard' => $buttons]] : [];

        $users = User::query()
            ->whereNotNull('telegram_id')
            ->whereNull('telegram_blocked_at')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            if (!$user->prefersTelegramNotification('daily_quote')) {
                continue;
            }

            SendTelegramNotificationJob::dispatch($user->id, $message, $options, 'daily_quote');
            $count++;
        }

        $this->info("Jami {$count} ta foydalanuvchiga kun hikmati navbatga yuborildi.");

        return Command::SUCCESS;
    }
}
