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

        $appUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/');
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

            // Spaced Repetition: Foydalanuvchining o'zi belgilagan (highlight/note) eski iqtibosini tekshirish
            $userHighlight = \App\Models\UserNote::where('user_id', $user->id)
                ->where('created_at', '<=', Carbon::now()->subDays(2))
                ->with('book')
                ->inRandomOrder()
                ->first();

            if (!$userHighlight) {
                $userAnnotation = \App\Models\PdfAnnotation::where('user_id', $user->id)
                    ->whereNotNull('note_text')
                    ->where('note_text', '!=', '')
                    ->where('created_at', '<=', Carbon::now()->subDays(2))
                    ->with('book')
                    ->inRandomOrder()
                    ->first();
            } else {
                $userAnnotation = null;
            }

            if ($userHighlight && $userHighlight->book && !empty($userHighlight->selected_text)) {
                $daysAgo = Carbon::parse($userHighlight->created_at)->diffInDays(Carbon::now());
                $daysAgoText = $daysAgo > 0 ? "{$daysAgo} kun oldin" : "bir necha kun oldin";
                $bookTitle = htmlspecialchars($userHighlight->book->title);
                $quoteSnippet = htmlspecialchars($userHighlight->selected_text);

                $userMsg = "🧠 <b>Eslaysizmi? Daily Review</b> 📚\n\n"
                         . "{$daysAgoText} <b>«{$bookTitle}»</b> kitobida ushbu fikrni belgilagan edingiz:\n\n"
                         . "«{$quoteSnippet}»\n\n"
                         . "— <i>Unutmaslik uchun qayta tafakkur qiling! ✨</i>";

                $userBtns = [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '📖 Kitobga o\'tish', 'url' => "{$appUrl}/books/{$userHighlight->book->slug}"]
                            ]
                        ]
                    ]
                ];

                SendTelegramNotificationJob::dispatch($user->id, $userMsg, $userBtns, 'daily_quote');
            } elseif ($userAnnotation && $userAnnotation->book) {
                $daysAgo = Carbon::parse($userAnnotation->created_at)->diffInDays(Carbon::now());
                $daysAgoText = $daysAgo > 0 ? "{$daysAgo} kun oldin" : "bir necha kun oldin";
                $bookTitle = htmlspecialchars($userAnnotation->book->title);
                $quoteSnippet = htmlspecialchars($userAnnotation->note_text);

                $userMsg = "🧠 <b>Eslaysizmi? Daily Review</b> 📚\n\n"
                         . "{$daysAgoText} <b>«{$bookTitle}»</b> kitobida quyidagi qaydni yozgan edingiz:\n\n"
                         . "«{$quoteSnippet}»\n\n"
                         . "— <i>Qayta xotirlash xotirani mustahkamlaydi! ✨</i>";

                $userBtns = [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '📖 Kitobga o\'tish', 'url' => "{$appUrl}/books/{$userAnnotation->book->slug}"]
                            ]
                        ]
                    ]
                ];

                SendTelegramNotificationJob::dispatch($user->id, $userMsg, $userBtns, 'daily_quote');
            } else {
                SendTelegramNotificationJob::dispatch($user->id, $message, $options, 'daily_quote');
            }

            $count++;
        }

        $this->info("Jami {$count} ta foydalanuvchiga kun hikmati navbatga yuborildi.");

        return Command::SUCCESS;
    }
}
