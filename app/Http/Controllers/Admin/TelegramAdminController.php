<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\TelegramStarPayment;
use App\Models\User;
use App\Services\Telegram\TelegramPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TelegramAdminController extends Controller
{
    /**
     * Admin Telegram boshqaruv paneli: bot holati, ommaviy xabar, Stars to'lovlari va telefon to'qnashuvlari.
     */
    public function index(TelegramPaymentService $paymentService)
    {
        $botConfigured = !empty(config('telegram.bot_token'));
        $botUsername = (string) config('telegram.bot_username', 'kitobxonS_bot');
        $paymentsEnabled = $paymentService->isPaymentsEnabled();
        $webhookConfigured = !empty(config('telegram.webhook_secret'));

        $stats = [
            'linked_users'     => User::whereNotNull('telegram_id')->count(),
            'active_users'     => User::whereNotNull('telegram_id')->whereNull('telegram_blocked_at')->count(),
            'blocked_users'    => User::whereNotNull('telegram_id')->whereNotNull('telegram_blocked_at')->count(),
            'unverified_phone' => User::whereNotNull('telegram_id')->whereNull('phone_verified_at')->count(),
            'stars_completed'  => (int) TelegramStarPayment::where('status', 'completed')->sum('stars_amount'),
            'stars_pending'    => TelegramStarPayment::where('status', 'pending')->count(),
            'stars_refunded'   => TelegramStarPayment::where('status', 'refunded')->count(),
        ];

        $payments = TelegramStarPayment::with('user')
            ->latest()
            ->paginate(15);

        $phoneCollisions = Cache::get('telegram_phone_collisions', []);
        $unverifiedUsers = User::whereNotNull('telegram_id')
            ->whereNull('phone_verified_at')
            ->latest()
            ->limit(15)
            ->get();

        $telegramUsers = User::whereNotNull('telegram_id')
            ->whereNull('telegram_blocked_at')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'phone', 'telegram_id', 'telegram_username', 'role']);

        return view('admin.telegram.index', compact(
            'botConfigured',
            'botUsername',
            'paymentsEnabled',
            'webhookConfigured',
            'stats',
            'payments',
            'phoneCollisions',
            'unverifiedUsers',
            'telegramUsers'
        ));
    }

    /**
     * Telegram bot nomidan barcha foydalanuvchilarga, ma'lum rolga yoki bitta tanlangan foydalanuvchiga e'lon/xabar yuborish.
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => 'nullable|in:all,single,role',
            'user_id'     => 'nullable|required_if:target_type,single|exists:users,id',
            'target_role' => 'nullable|required_if:target_type,role|in:admin,teacher,student,reader',
            'title'       => 'nullable|string|max:120',
            'message'     => 'required|string|min:2|max:3500',
            'button_text' => 'nullable|string|max:64',
            'button_url'  => 'nullable|url|max:255',
        ]);

        if (empty(config('telegram.bot_token'))) {
            return back()->with('error', 'TELEGRAM_BOT_TOKEN sozlanmagan. Xabar yuborib bo\'lmaydi.');
        }

        $targetType = $validated['target_type'] ?? 'all';
        $rawTitle = trim((string) ($validated['title'] ?? ''));
        $rawMessage = trim((string) $validated['message']);

        $formattedTelegramMessage = $rawTitle !== ''
            ? "📢 <b>" . htmlspecialchars($rawTitle) . "</b>\n\n" . $rawMessage
            : $rawMessage;

        $inAppTitle = $rawTitle !== '' ? $rawTitle : ($targetType === 'single' ? '📩 Ma\'muriyatdan shaxsiy xabar' : '📢 Rasmiy e\'lon');
        $inAppLink = $validated['button_url'] ?? '/dashboard';

        $options = [];
        if (!empty($validated['button_text']) && !empty($validated['button_url'])) {
            $options['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => $validated['button_text'],
                            'url'  => $validated['button_url'],
                        ],
                    ],
                ],
            ];
        }

        // 1. Bitta tanlangan foydalanuvchiga bot orqali yuborish
        if ($targetType === 'single') {
            $targetUser = User::findOrFail($validated['user_id']);
            $safeUserName = htmlspecialchars((string) $targetUser->name);
            $titleLine = $rawTitle !== '' ? "📌 <b>" . htmlspecialchars($rawTitle) . "</b>\n\n" : '';

            $singleTelegramMessage = "📩 <b>Admin sizga shaxsiy xabar yubordi:</b>\n\n"
                . "Hurmatli <b>{$safeUserName}</b>,\n\n"
                . $titleLine
                . $rawMessage;

            $singleInAppTitle = '📩 Admin sizga: ' . ($rawTitle !== '' ? $rawTitle : 'Shaxsiy xabar');

            \App\Services\NotifyUser::send($targetUser, 'system', $singleInAppTitle, strip_tags($rawMessage), '📩', $inAppLink);

            if (!$targetUser->isTelegramLinked() || $targetUser->isTelegramBlocked()) {
                return back()->with('error', "'{$targetUser->name}' Telegram botni ulamagan yoki bloklagan. Xabar sayt ichidagi bildirishnomalariga yuborildi.");
            }

            SendTelegramNotificationJob::dispatch(
                $targetUser->id,
                $singleTelegramMessage,
                $options,
                'security'
            );

            $botUsername = config('telegram.bot_username', 'kitobxonS_bot');
            return back()->with('success', "Xabar rasmiy @{$botUsername} boti orqali '{$targetUser->name}' (@{$targetUser->username}) ga muvaffaqiyatli yuborildi! ✈️");
        }

        // 2. Barcha foydalanuvchilarga yoki ma'lum roldagilarga ommaviy e'lon yuborish
        $query = User::whereNotNull('telegram_id')
            ->whereNull('telegram_blocked_at')
            ->where('is_banned', false);

        if ($targetType === 'role' && !empty($validated['target_role'])) {
            $role = $validated['target_role'];
            $query->where(function ($q) use ($role) {
                $q->where('role', $role)
                  ->orWhereHas('roles', fn ($rq) => $rq->where('name', $role));
            });
        }

        $count = 0;
        $query->select(['id', 'name', 'username', 'telegram_id', 'notification_prefs', 'telegram_blocked_at'])
            ->chunkById(100, function ($users) use ($formattedTelegramMessage, $rawMessage, $inAppTitle, $inAppLink, $options, &$count) {
                foreach ($users as $user) {
                    \App\Services\NotifyUser::send($user, 'system', $inAppTitle, strip_tags($rawMessage), '📢', $inAppLink);

                    SendTelegramNotificationJob::dispatch(
                        $user->id,
                        $formattedTelegramMessage,
                        $options,
                        'broadcast'
                    );
                    $count++;
                }
            });

        return back()->with('success', "Ommaviy e'lon Telegram bot orqali {$count} ta faol foydalanuvchiga muvaffaqiyatli yuborildi! 🚀");
    }

    /**
     * Foydalanuvchilar ro'yxati (/admin/users) yoki profilidan bitta foydalanuvchiga Telegram Bot orqali xabar yuborish.
     */
    public function sendToUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:120',
            'message'     => 'required|string|min:2|max:3500',
            'button_text' => 'nullable|string|max:64',
            'button_url'  => 'nullable|url|max:255',
        ]);

        $rawTitle = trim((string) ($validated['title'] ?? ''));
        $rawMessage = trim((string) $validated['message']);
        $safeUserName = htmlspecialchars((string) $user->name);
        $titleLine = $rawTitle !== '' ? "📌 <b>" . htmlspecialchars($rawTitle) . "</b>\n\n" : '';

        $tgText = "📩 <b>Admin sizga shaxsiy xabar yubordi:</b>\n\n"
            . "Hurmatli <b>{$safeUserName}</b>,\n\n"
            . $titleLine
            . $rawMessage;

        $inAppTitle = '📩 Admin sizga: ' . ($rawTitle !== '' ? $rawTitle : 'Shaxsiy xabar');
        $inAppLink = $validated['button_url'] ?? '/dashboard';

        // Sayt ichiga ham bildirishnoma yuboramiz
        \App\Services\NotifyUser::send($user, 'system', $inAppTitle, strip_tags($rawMessage), '📩', $inAppLink);

        if (empty(config('telegram.bot_token'))) {
            return back()->with('error', 'TELEGRAM_BOT_TOKEN sozlanmagan. Xabar faqat sayt ichida yuborildi.');
        }

        if (!$user->isTelegramLinked() || $user->isTelegramBlocked()) {
            return back()->with('error', "'{$user->name}' hali Telegram botni ulamagan (yoki bloklagan). Xabar uning saytdagi bildirishnomalariga yuborildi.");
        }

        $options = [];
        if (!empty($validated['button_text']) && !empty($validated['button_url'])) {
            $options['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => $validated['button_text'],
                            'url'  => $validated['button_url'],
                        ],
                    ],
                ],
            ];
        }

        try {
            SendTelegramNotificationJob::dispatch(
                $user->id,
                $tgText,
                $options,
                'security'
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Telegram bot orqali yuborishda xatolik: ' . $e->getMessage());
        }

        $botUsername = config('telegram.bot_username', 'kitobxonS_bot');
        return back()->with('success', "Xabar shaxsiy lichkadan emas, rasmiy @{$botUsername} boti orqali '{$user->name}' ga muvaffaqiyatli yuborildi! ✈️");
    }

    /**
     * Telegram Stars tranzaksiyalarini getStarTransactions bilan solishtirish.
     */
    public function reconcile(TelegramPaymentService $paymentService): RedirectResponse
    {
        if (empty(config('telegram.bot_token'))) {
            return back()->with('error', 'TELEGRAM_BOT_TOKEN sozlanmagan.');
        }

        $result = $paymentService->reconcileStarTransactions(100);

        if (!$result['success']) {
            return back()->with('error', 'Stars tranzaksiyalarini solishtirishda xatolik: ' . ($result['message'] ?? 'Noma\'lum xato'));
        }

        return back()->with('success', "Stars solishtiruvi yakunlandi: {$result['checked']} ta tranzaksiya tekshirildi, {$result['fulfilled']} ta to'lov tiklandi.");
    }

    /**
     * Yakunlangan Telegram Stars to'lovini qaytarish (Refund).
     */
    public function refund(Request $request, TelegramStarPayment $payment, TelegramPaymentService $paymentService): RedirectResponse
    {
        $reason = $request->input('reason', 'Admin tomonidan qaytarildi');
        $result = $paymentService->refundPayment($payment, $reason);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
