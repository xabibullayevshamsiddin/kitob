<?php

namespace App\Services\Telegram;

use App\Models\User;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Exceptions\TelegramBlockedException;
use App\Services\Telegram\Exceptions\TelegramInvalidTokenException;
use App\Services\Telegram\Exceptions\TelegramRateLimitException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramClient
{
    protected string $botToken;
    protected string $apiUrl;
    protected int $timeout;
    protected int $retryAttempts;

    public function __construct(?string $botToken = null)
    {
        $this->botToken = $botToken ?? (string) config('telegram.bot_token', '');

        if (empty($this->botToken)) {
            throw new TelegramInvalidTokenException();
        }

        $this->apiUrl = rtrim((string) config('telegram.api_url', 'https://api.telegram.org'), '/');
        $this->timeout = (int) config('telegram.timeout', 15);
        $this->retryAttempts = (int) config('telegram.retry_attempts', 2);
    }

    /**
     * Telegram chatiga xabar yuborish (sendMessage).
     */
    public function sendMessage(int|string $chatId, string $text, array $options = []): array
    {
        $payload = array_merge([
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ], $options);

        try {
            return $this->request('sendMessage', $payload, $chatId);
        } catch (TelegramApiException $e) {
            // Lokal muhitda (http://localhost) Telegram inline_keyboard ichidagi url tugmalarni rad etsa,
            // url tugmalarni matnga ilova qilib, xabarni xatosiz yetkazib beramiz.
            if ($e->getCode() === 400 && isset($payload['reply_markup']['inline_keyboard'])) {
                $fallbackUrls = [];
                $cleanRows = [];

                foreach ($payload['reply_markup']['inline_keyboard'] as $row) {
                    $cleanRow = [];
                    foreach ($row as $btn) {
                        if (isset($btn['url'])) {
                            $fallbackUrls[] = ($btn['text'] ?? 'Havola') . ': ' . $btn['url'];
                        } else {
                            $cleanRow[] = $btn;
                        }
                    }
                    if (!empty($cleanRow)) {
                        $cleanRows[] = $cleanRow;
                    }
                }

                if (!empty($fallbackUrls)) {
                    $payload['text'] .= "\n\n" . implode("\n", $fallbackUrls);
                }

                if (!empty($cleanRows)) {
                    $payload['reply_markup']['inline_keyboard'] = $cleanRows;
                } else {
                    unset($payload['reply_markup']);
                }

                return $this->request('sendMessage', $payload, $chatId);
            }

            throw $e;
        }
    }

    /**
     * Telegram chatiga yoki kanaliga rasm bilan xabar yuborish (sendPhoto).
     */
    public function sendPhoto(int|string $chatId, string $photoUrl, string $caption = '', array $options = []): array
    {
        $payload = array_merge([
            'chat_id'    => $chatId,
            'photo'      => $photoUrl,
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ], $options);

        try {
            return $this->request('sendPhoto', $payload, $chatId);
        } catch (\Throwable $e) {
            // Agar rasm yuborishda xatolik bo'lsa (URL formati yoki rasm hajmi), xabar matn ko'rinishida yuboriladi
            return $this->sendMessage($chatId, $caption, $options);
        }
    }

    /**
     * Mavjud xabar matnini va inline tugmalarini tahrirlash (editMessageText).
     */
    public function editMessageText(int|string $chatId, int $messageId, string $text, array $options = []): array
    {
        $payload = array_merge([
            'chat_id'                  => $chatId,
            'message_id'               => $messageId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ], $options);

        return $this->request('editMessageText', $payload, $chatId);
    }

    /**
     * Inline tugma bosilganda callback so'roviga javob berish (answerCallbackQuery).
     */
    public function answerCallbackQuery(string $callbackQueryId, array $options = []): array
    {
        $payload = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options);

        return $this->request('answerCallbackQuery', $payload);
    }

    /**
     * Webhook o'rnatish (setWebhook).
     */
    public function setWebhook(string $url, array $options = []): array
    {
        $secretToken = $options['secret_token'] ?? config('telegram.webhook_secret');

        $payload = array_merge([
            'url' => $url,
        ], $options);

        if (!empty($secretToken) && !isset($payload['secret_token'])) {
            $payload['secret_token'] = $secretToken;
        }

        return $this->request('setWebhook', $payload);
    }

    /**
     * Webhook'ni o'chirish (deleteWebhook).
     */
    public function deleteWebhook(array $options = []): array
    {
        return $this->request('deleteWebhook', $options);
    }

    /**
     * Joriy webhook holati haqida ma'lumot olish (getWebhookInfo).
     */
    public function getWebhookInfo(): array
    {
        return $this->request('getWebhookInfo');
    }

    /**
     * Yangilanishlarni so'rab olish (Polling rejimi uchun getUpdates).
     */
    public function getUpdates(array $options = []): array
    {
        return $this->request('getUpdates', $options);
    }

    /**
     * Bot menyusi buyruqlarini sozlash (setMyCommands).
     */
    public function setMyCommands(array $commands, array $options = []): array
    {
        $payload = array_merge([
            'commands' => $commands,
        ], $options);

        return $this->request('setMyCommands', $payload);
    }

    /**
     * Botning chap pastki menyu tugmasini sozlash (setChatMenuButton).
     * Masalan, Mini App (web_app) tugmasi yoki standart buyruqlar menyusi.
     */
    public function setChatMenuButton(?array $menuButton = null, int|string|null $chatId = null): array
    {
        $payload = [];
        if ($chatId !== null) {
            $payload['chat_id'] = $chatId;
        }

        if ($menuButton !== null) {
            $payload['menu_button'] = $menuButton;
        }

        return $this->request('setChatMenuButton', $payload);
    }

    /**
     * Joriy menyu tugmasi holatini olish (getChatMenuButton).
     */
    public function getChatMenuButton(int|string|null $chatId = null): array
    {
        $payload = [];
        if ($chatId !== null) {
            $payload['chat_id'] = $chatId;
        }

        return $this->request('getChatMenuButton', $payload);
    }

    /**
     * Telegram Stars yoki boshqa to'lovlar uchun invoice havolasini yaratish (createInvoiceLink).
     */
    public function createInvoiceLink(
        string $title,
        string $description,
        string $payload,
        string $currency,
        array $prices,
        array $options = []
    ): string {
        $data = array_merge([
            'title'          => $title,
            'description'    => $description,
            'payload'        => $payload,
            'currency'       => $currency,
            'prices'         => $prices,
            'provider_token' => '', // Stars (XTR) uchun bo'sh qoldiriladi
        ], $options);

        $response = $this->request('createInvoiceLink', $data);

        return (string) ($response['result'] ?? '');
    }

    /**
     * Telegram to'lov jarayonini tasdiqlash yoki rad etish (answerPreCheckoutQuery).
     */
    public function answerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, ?string $errorMessage = null): array
    {
        $payload = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok'                    => $ok,
        ];

        if (!$ok && $errorMessage !== null) {
            $payload['error_message'] = $errorMessage;
        }

        return $this->request('answerPreCheckoutQuery', $payload);
    }

    /**
     * Bot haqida ma'lumot olish (getMe).
     */
    public function getMe(): array
    {
        return $this->request('getMe');
    }

    /**
     * Telegram Stars to'lovini qaytarish (refundStarPayment).
     */
    public function refundStarPayment(int|string $userId, string $telegramPaymentChargeId): array
    {
        $payload = [
            'user_id'                    => (int) $userId,
            'telegram_payment_charge_id' => $telegramPaymentChargeId,
        ];

        return $this->request('refundStarPayment', $payload);
    }

    /**
     * Botning Telegram Stars tranzaksiyalari ro'yxatini olish (getStarTransactions).
     */
    public function getStarTransactions(int $offset = 0, int $limit = 100): array
    {
        $payload = [
            'offset' => max(0, $offset),
            'limit'  => min(100, max(1, $limit)),
        ];

        return $this->request('getStarTransactions', $payload);
    }

    /**
     * Telegram Bot API'ga POST so'rov yuborish.
     *
     * @param string $method
     * @param array $payload
     * @param int|string|null $chatId
     * @return array
     *
     * @throws TelegramBlockedException
     * @throws TelegramRateLimitException
     * @throws TelegramApiException
     */
    protected function request(string $method, array $payload = [], int|string|null $chatId = null): array
    {
        $url = $this->buildUrl($method);
        $requestTimeout = isset($payload['timeout']) ? ((int) $payload['timeout'] + 10) : $this->timeout;

        try {
            $response = Http::connectTimeout(15)
                ->timeout($requestTimeout)
                ->retry($this->retryAttempts, 200, function ($exception) {
                    // Agar HTTP xato javobi qaytgan bo'lsa (403, 429 va h.k.), qayta so'rov yubormaymiz
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        return false;
                    }
                    return true;
                }, false)
                ->asJson()
                ->post($url, $payload);
        } catch (\Throwable $e) {
            if ($e instanceof TelegramApiException) {
                throw $e;
            }

            if ($e instanceof \Illuminate\Http\Client\RequestException && $e->response) {
                return $this->handleResponse($e->response, $method, $chatId ?? ($payload['chat_id'] ?? null));
            }

            Log::error('Telegram API ulanish xatosi: ' . $this->maskToken($e->getMessage()), [
                'method' => $method,
            ]);

            throw new TelegramApiException(
                'Telegram API so\'rovida ulanish xatosi yuz berdi: ' . $this->maskToken($e->getMessage()),
                (int) $e->getCode(),
                null,
                null,
                [],
                $e
            );
        }

        return $this->handleResponse($response, $method, $chatId ?? ($payload['chat_id'] ?? null));
    }

    /**
     * HTTP javobini tekshirish va xatoliklarni maxsus istisnolarga aylantirish.
     */
    protected function handleResponse(Response $response, string $method, int|string|null $chatId = null): array
    {
        $data = $response->json();
        $isOk = is_array($data) && ($data['ok'] ?? false) === true;

        if ($isOk) {
            return $data;
        }

        $httpStatus = $response->status();
        $errorCode = is_array($data) ? ($data['error_code'] ?? $httpStatus) : $httpStatus;
        $description = is_array($data) ? ($data['description'] ?? 'Noma\'lum xatolik') : $response->body();
        $parameters = is_array($data) ? ($data['parameters'] ?? []) : [];

        // 1. Bot foydalanuvchi tomonidan bloklangan (403 Forbidden)
        if ($httpStatus === 403 || $errorCode === 403 || str_contains(strtolower($description), 'blocked by the user')) {
            $this->handleUserBlocked($chatId);
            throw new TelegramBlockedException($chatId, $description, $errorCode);
        }

        // 2. So'rovlar chegarasi oshib ketdi (429 Too Many Requests)
        if ($httpStatus === 429 || $errorCode === 429 || isset($parameters['retry_after'])) {
            $retryAfter = (int) ($parameters['retry_after'] ?? 1);
            throw new TelegramRateLimitException($retryAfter, $description, $errorCode, $parameters);
        }

        // 3. Boshqa API xatoliklari
        Log::warning('Telegram API xatoligi: ' . $description, [
            'method'     => $method,
            'status'     => $httpStatus,
            'error_code' => $errorCode,
        ]);

        throw new TelegramApiException(
            "Telegram API xatosi [{$errorCode}]: {$description}",
            $httpStatus,
            $errorCode,
            $description,
            $parameters
        );
    }

    /**
     * Bot bloklanganda foydalanuvchining telegram_blocked_at ustuniga now() yozish.
     */
    protected function handleUserBlocked(int|string|null $chatId): void
    {
        if (empty($chatId)) {
            return;
        }

        try {
            User::where('telegram_id', $chatId)
                ->whereNull('telegram_blocked_at')
                ->update(['telegram_blocked_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Foydalanuvchi telegram_blocked_at yangilashda xato: ' . $e->getMessage());
        }
    }

    /**
     * Telegram Bot API to'liq URL manzilini hosil qilish.
     */
    protected function buildUrl(string $method): string
    {
        return "{$this->apiUrl}/bot{$this->botToken}/{$method}";
    }

    /**
     * Loglar yoki xabarlardagi bot tokenini yashirish.
     */
    public function maskToken(string $text): string
    {
        if (empty($this->botToken)) {
            return $text;
        }

        return str_replace($this->botToken, '***MASKED_TOKEN***', $text);
    }
}
