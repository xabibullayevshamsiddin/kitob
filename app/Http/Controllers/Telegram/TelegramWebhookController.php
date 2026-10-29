<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Telegram webhook so'rovini qabul qilish va qayta ishlash.
     */
    public function handle(Request $request, \App\Services\Telegram\TelegramBotService $botService): JsonResponse
    {
        // 1. Maxfiy sarlavhani tekshirish (X-Telegram-Bot-Api-Secret-Token)
        $configuredSecret = (string) config('telegram.webhook_secret', '');
        $headerSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if (empty($configuredSecret) || !hash_equals($configuredSecret, $headerSecret)) {
            Log::warning('Telegram webhook: Maxfiy token mos kelmadi yoki topilmadi.', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'ok'    => false,
                'error' => 'Unauthorized secret token',
            ], 403);
        }

        // 2. So'rov tanasini tekshirish
        $updateId = $request->input('update_id');
        if (empty($updateId)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Missing update_id',
            ], 400);
        }

        // 3. De-duplication: Agar ushbu update_id allaqachon muvaffaqiyatli qayta ishlangan bo'lsa
        if (TelegramUpdate::hasBeenProcessed($updateId)) {
            Log::info("Telegram webhook: update_id #{$updateId} avval qayta ishlangan (de-duplicated).");

            return response()->json([
                'ok'        => true,
                'duplicate' => true,
            ]);
        }

        // Opportunistic expired bans check on incoming Telegram activity
        if (Cache::add('process_expired_bans_tg_lock', 1, 30)) {
            try {
                \App\Models\User::processExpiredBans();
            } catch (\Throwable $e) {
                Log::warning('Telegram webhook expired bans error: ' . $e->getMessage());
            }
        }

        // Parallel bir xil update so'rovlarini ushlab qolish (15 soniyalik qulf)
        $lockKey = "tg_update_lock_{$updateId}";
        if (!Cache::add($lockKey, true, now()->addSeconds(15))) {
            return response()->json([
                'ok'      => false,
                'message' => 'Update is currently being processed',
            ], 429);
        }

        // 4. Update turini va chat ID'sini aniqlash
        $updateType = $this->determineUpdateType($request);
        $chatId = $this->extractChatId($request, $updateType);

        // 5. Bot xizmatiga yangilanishni qayta ishlash uchun uzatish
        try {
            $botService->handleUpdate($request->all());

            // Faqat muvaffaqiyatli ishlangandan so'ng de-duplication bazasiga qayd etamiz
            TelegramUpdate::recordIfNew($updateId, $chatId, $updateType);

            Cache::forget($lockKey);

            Log::info("Telegram webhook: update_id #{$updateId} muvaffaqiyatli qayta ishlandi.", [
                'update_id'   => $updateId,
                'type'        => $updateType,
                'chat_id'     => $chatId,
            ]);

            return response()->json([
                'ok' => true,
            ]);
        } catch (\Throwable $e) {
            Cache::forget($lockKey);

            Log::error("Telegram bot xabarni qayta ishlashda xato yuz berdi: " . $e->getMessage(), [
                'update_id' => $updateId,
                'trace'     => $e->getTraceAsString(),
            ]);

            // Xatolikda 500 javobini qaytaramiz — shunda Telegram ushbu xabarni qaytadan jo'natadi (retry)
            return response()->json([
                'ok'    => false,
                'error' => 'Processing failed, please retry',
            ], 500);
        }
    }

    /**
     * Update turini aniqlash.
     */
    protected function determineUpdateType(Request $request): string
    {
        foreach ([
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
            'shipping_query',
            'pre_checkout_query',
            'poll',
            'poll_answer',
            'my_chat_member',
            'chat_member',
            'chat_join_request',
        ] as $type) {
            if ($request->has($type)) {
                return $type;
            }
        }

        return 'unknown';
    }

    /**
     * So'rovdan chat ID'ni ajratib olish.
     */
    protected function extractChatId(Request $request, string $type): ?int
    {
        if ($request->has('message.chat.id')) {
            return (int) $request->input('message.chat.id');
        }

        if ($request->has('callback_query.message.chat.id')) {
            return (int) $request->input('callback_query.message.chat.id');
        }

        if ($request->has('callback_query.from.id')) {
            return (int) $request->input('callback_query.from.id');
        }

        if ($request->has('pre_checkout_query.from.id')) {
            return (int) $request->input('pre_checkout_query.from.id');
        }

        if ($request->has('my_chat_member.chat.id')) {
            return (int) $request->input('my_chat_member.chat.id');
        }

        return null;
    }
}
