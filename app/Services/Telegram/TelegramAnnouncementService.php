<?php

namespace App\Services\Telegram;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramAnnouncementService
{
    protected ?TelegramClient $client = null;

    public function __construct(?TelegramClient $client = null)
    {
        if ($client !== null) {
            $this->client = $client;
        } else {
            try {
                $token = config('telegram.bot_token');
                if (!empty($token)) {
                    $this->client = new TelegramClient($token);
                }
            } catch (\Throwable $e) {
                Log::warning("Telegram client yaratishda xatolik: " . $e->getMessage());
                $this->client = null;
            }
        }
    }

    /**
     * Yangi kitob qo'shilganda Telegram kanalga va barcha bot foydalanuvchilariga e'lon yuborish.
     */
    public function broadcastNewBook(Book $book, bool $toChannel = true, bool $toUsers = true): array
    {
        if (empty(config('telegram.bot_token')) || !$this->client) {
            return ['channel' => false, 'users_count' => 0, 'reason' => 'bot_token_empty'];
        }

        $appUrl = rtrim(config('app.url', 'https://kitobxon.elitehost.uz'), '/');
        $bookUrl = route('books.show', $book->slug);
        $miniAppUrl = config('telegram.mini_app_url', "{$appUrl}/tg/app");

        $desc = Str::limit(strip_tags($book->description ?? ''), 280);

        $safeTitle = htmlspecialchars($book->title ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAuthor = htmlspecialchars($book->author ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeGenre = htmlspecialchars($book->genre ?? 'Boshqa', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDesc = htmlspecialchars($desc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = "📚 <b>Yangi kitob kutubxonamizga qo'shildi!</b>\n\n"
              . "📖 <b>«{$safeTitle}»</b>\n"
              . "✍️ <b>Muallif:</b> {$safeAuthor}\n"
              . "🏷️ <b>Janr:</b> {$safeGenre}\n\n"
              . (!empty($safeDesc) ? "📝 <i>{$safeDesc}</i>\n\n" : "")
              . "🔥 Hoziroq mutolaani boshlang va kunlik <b>Streak</b> hamda oltin tangalarga ega bo'ling!";

        $buttons = [
            [
                ['text' => '📖 Mutolaa qilish', 'url' => $bookUrl],
            ]
        ];

        if (!empty($miniAppUrl) && str_starts_with($miniAppUrl, 'https://')) {
            $buttons[] = [
                ['text' => '📱 Mini Appda ochish', 'web_app' => ['url' => $miniAppUrl]],
            ];
        }

        $keyboard = [
            'inline_keyboard' => $buttons,
        ];

        $coverUrl = $book->cover_url;
        $isRemoteImage = !empty($coverUrl) && (str_starts_with($coverUrl, 'http://') || str_starts_with($coverUrl, 'https://'));

        $channelSent = false;

        // 1. Telegram Kanalga yuborish (agar sozlangan bo'lsa)
        if ($toChannel) {
            $channelId = config('telegram.channel_id') 
                      ?: (function_exists('setting') ? setting('telegram_channel_id', '') : null)
                      ?: env('TELEGRAM_CHANNEL_ID', null);

            if (!empty($channelId)) {
                try {
                    if ($isRemoteImage) {
                        $this->client->sendPhoto($channelId, $coverUrl, $text, [
                            'reply_markup' => $keyboard,
                        ]);
                    } else {
                        $this->client->sendMessage($channelId, $text, [
                            'reply_markup' => $keyboard,
                        ]);
                    }
                    $channelSent = true;
                } catch (\Throwable $e) {
                    Log::warning("Telegram kanalga e'lon yuborilmadi: " . $e->getMessage());
                }
            }
        }

        // 2. Barcha faol bot foydalanuvchilariga yuborish
        $sentUsersCount = 0;
        if ($toUsers) {
            $users = User::whereNotNull('telegram_id')
                ->whereNull('telegram_blocked_at')
                ->select(['id', 'telegram_id', 'notification_prefs'])
                ->get();

            foreach ($users as $user) {
                try {
                    SendTelegramNotificationJob::dispatch(
                        $user->id,
                        $text,
                        ['reply_markup' => $keyboard],
                        'new_book'
                    );
                    $sentUsersCount++;
                } catch (\Throwable $e) {
                    // Xatolik yuz bersa ham boshqa foydalanuvchilarga jo'natish davom etadi
                }
            }
        }

        return [
            'channel'     => $channelSent,
            'users_count' => $sentUsersCount,
        ];
    }

    /**
     * Yangi test (quiz) qo'shilganda Telegram kanalga va barcha bot foydalanuvchilariga e'lon yuborish.
     */
    public function broadcastNewQuiz(Quiz $quiz, bool $toChannel = true, bool $toUsers = true): array
    {
        if (empty(config('telegram.bot_token')) || !$this->client) {
            return ['channel' => false, 'users_count' => 0, 'reason' => 'bot_token_empty'];
        }

        $quizUrl = route('quiz.show', $quiz->book_id ?? $quiz->id);
        $bookTitle = $quiz->book ? "«{$quiz->book->title}» kitobi" : "Umumiy adabiyot";
        $qCount = $quiz->questions()->count();

        $safeTitle = htmlspecialchars($quiz->title ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeBookTitle = htmlspecialchars($bookTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = "🧠 <b>Yangi test sinovi platformada!</b>\n\n"
              . "🏆 <b>Test:</b> {$safeTitle}\n"
              . "📚 <b>Asoslangan:</b> {$safeBookTitle}\n"
              . "❓ <b>Savollar soni:</b> {$qCount} ta savol\n"
              . "🪙 <b>Mukofot:</b> Har bir to'g'ri javob uchun oltin tangalar!\n\n"
              . "Bilimingizni sinab ko'ring va umumiy reytingda peshqadamlar qatoriga qo'shiling! 🥇";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🎯 Testni topshirish', 'url' => $quizUrl],
                ],
            ],
        ];

        $channelSent = false;

        // 1. Kanalga yuborish
        if ($toChannel) {
            $channelId = config('telegram.channel_id') 
                      ?: (function_exists('setting') ? setting('telegram_channel_id', '') : null)
                      ?: env('TELEGRAM_CHANNEL_ID', null);

            if (!empty($channelId)) {
                try {
                    $this->client->sendMessage($channelId, $text, [
                        'reply_markup' => $keyboard,
                    ]);
                    $channelSent = true;
                } catch (\Throwable $e) {
                    Log::warning("Telegram kanalga test e'loni yuborilmadi: " . $e->getMessage());
                }
            }
        }

        // 2. Bot foydalanuvchilariga yuborish
        $sentUsersCount = 0;
        if ($toUsers) {
            $users = User::whereNotNull('telegram_id')
                ->whereNull('telegram_blocked_at')
                ->select(['id', 'telegram_id', 'notification_prefs'])
                ->get();

            foreach ($users as $user) {
                try {
                    SendTelegramNotificationJob::dispatch(
                        $user->id,
                        $text,
                        ['reply_markup' => $keyboard],
                        'new_quiz'
                    );
                    $sentUsersCount++;
                } catch (\Throwable $e) {}
            }
        }

        return [
            'channel'     => $channelSent,
            'users_count' => $sentUsersCount,
        ];
    }
}
