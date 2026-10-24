<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Exceptions\TelegramBlockedException;
use App\Services\Telegram\Exceptions\TelegramRateLimitException;
use App\Services\Telegram\TelegramClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $message;
    public array $options;
    public ?string $type;

    /**
     * Bajarish urinishlari soni.
     */
    public int $tries = 3;

    /**
     * @param int $userId
     * @param string $message
     * @param array $options Telegram sendMessage options (masalan inline_keyboard)
     * @param string|null $type streak_reminder, new_book, coin_reward, security va h.k.
     */
    public function __construct(int $userId, string $message, array $options = [], ?string $type = null)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->options = $options;
        $this->type = $type;
    }

    public function handle(?TelegramClient $client = null): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        // Foydalanuvchi botni ulamagan, bloklagan yoki ushbu turdagi bildirishnomani o'chirgan bo'lsa
        if (!$user->prefersTelegramNotification($this->type)) {
            Log::info("Telegram bildirishnomasi bekor qilindi (foydalanuvchi sozlamasi): User #{$user->id}, tur: " . ($this->type ?? 'general'));
            return;
        }

        // Jim soatlar (Quiet Hours: 23:00 - 07:00 Asia/Tashkent) tekshiruvi (security bundan mustasno)
        if ($this->type !== 'security' && $user->isInTelegramQuietHours()) {
            Log::info("Telegram bildirishnomasi jim soatlar sababli yuborilmadi: User #{$user->id}, tur: " . ($this->type ?? 'general'));
            return;
        }

        if ($client === null) {
            if (empty(config('telegram.bot_token'))) {
                return;
            }
            $client = new TelegramClient();
        }

        $options = $this->options;

        // Tungi vaqtda (23:00 - 07:00) ovozsiz bildirishnoma (disable_notification)
        $currentHour = (int) now('Asia/Tashkent')->format('G');
        if ($this->type !== 'security' && ($currentHour >= 23 || $currentHour < 7) && !isset($options['disable_notification'])) {
            $options['disable_notification'] = true;
        }

        try {
            $client->sendMessage($user->telegram_id, $this->message, $options);
        } catch (TelegramBlockedException $e) {
            Log::warning("Telegram bot foydalanuvchi tomonidan bloklangan: User #{$user->id}");
            $user->markTelegramBlocked();
        } catch (TelegramRateLimitException $e) {
            Log::warning("Telegram rate limit: {$e->getRetryAfter()} soniya kutilmoqda. Job navbatga qaytarildi.");
            $this->release($e->getRetryAfter());
        } catch (TelegramApiException $e) {
            Log::error("Telegram bildirishnoma yuborishda xatolik: " . $e->getMessage(), [
                'user_id' => $user->id,
                'type'    => $this->type,
            ]);
            throw $e;
        }
    }
}
