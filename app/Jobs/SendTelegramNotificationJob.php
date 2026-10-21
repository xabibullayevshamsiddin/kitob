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

    public function handle(TelegramClient $client): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        // Foydalanuvchi botni ulamagan yoki bloklagan bo'lsa
        if (!$user->isTelegramLinked() || $user->isTelegramBlocked()) {
            return;
        }

        // Foydalanuvchi ushbu turdagi bildirishnomani o'chirib qo'ygan bo'lsa
        if ($this->type !== null && !$user->prefersTelegramNotification($this->type)) {
            Log::info("Telegram bildirishnomasi bekor qilindi (foydalanuvchi sozlamasi): User #{$user->id}, tur: {$this->type}");
            return;
        }

        try {
            $client->sendMessage($user->telegram_id, $this->message, $this->options);
        } catch (TelegramBlockedException $e) {
            Log::warning("Telegram bot foydalanuvchi tomonidan bloklangan: User #{$user->id}, TG: {$user->telegram_id}");
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
