<?php

namespace App\Console\Commands\Telegram;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendStreakRemindersCommand extends Command
{
    protected $signature = 'telegram:send-streak-reminders';

    protected $description = 'Bugun hali kitob o\'qimagan va streaki uzilish arafasida bo\'lgan foydalanuvchilarga eslatma yuborish';

    public function handle(): int
    {
        $today = Carbon::now('Asia/Tashkent')->toDateString();

        $this->info("Streak eslatmalari tekshirilmoqda ({$today})...");

        // Telegram ulangan, bloklanmagan va streakiga ega foydalanuvchilar
        $users = User::query()
            ->whereNotNull('telegram_id')
            ->whereNull('telegram_blocked_at')
            ->whereHas('streak', function ($query) use ($today) {
                $query->where('current_streak', '>', 0)
                      ->where(function ($sub) use ($today) {
                          $sub->whereNull('last_active_date')
                              ->orWhereDate('last_active_date', '<', $today);
                      });
            })
            ->with('streak')
            ->get();

        $count = 0;
        $appUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/');

        foreach ($users as $user) {
            if (!$user->prefersTelegramNotification('streak_reminder')) {
                continue;
            }

            $streak = $user->streak?->current_streak ?? 1;
            $name = htmlspecialchars($user->name);

            // Foydalanuvchining oxirgi mutolaa qilgan kitobini aniqlash
            $lastProgress = \App\Models\BookReadingProgress::where('user_id', $user->id)
                ->with('book')
                ->orderByDesc('updated_at')
                ->first();

            if ($lastProgress && $lastProgress->book) {
                $bookTitle = htmlspecialchars($lastProgress->book->title);
                $page = (int) ($lastProgress->current_page ?? 1);
                $bookUrl = "{$appUrl}/books/{$lastProgress->book->slug}";

                $message = "🔥 <b>Sizni sog'indik, {$name}!</b>\n\n"
                         . "<b>«{$bookTitle}»</b> kitobida <b>{$page}-sahifada</b> to'xtagan edingiz.\n\n"
                         . "Yana 5 daqiqa mutolaa qilib, bugungi <b>{$streak} kunlik streak'ingizni</b> tiklang! 🚀📖";

                $options = [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => "📖 Mutolaani davom ettirish ({$page}-bet)", 'url' => $bookUrl]
                            ]
                        ]
                    ]
                ];
            } else {
                $message = "🔥 <b>Olovni saqlab qoling, {$name}!</b>\n\n"
                         . "Sizning <b>{$streak} kunlik</b> Streak silsilangiz bor! Lekin bugun hali kitob o'qimadingiz.\n\n"
                         . "Kun yakunlanishiga oz fursat qoldi. Hoziroq bir necha sahifa mutolaa qiling va ketma-ketlikni boy bermang! 📖✨";

                $options = [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '📖 Hoziroq mutolaa qilish', 'url' => $appUrl]
                            ]
                        ]
                    ]
                ];
            }

            SendTelegramNotificationJob::dispatch($user->id, $message, $options, 'streak_reminder');
            $count++;
        }

        $this->info("Jami {$count} ta foydalanuvchiga Streak eslatmasi navbatga yuborildi.");

        return Command::SUCCESS;
    }
}
