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

        return view('admin.telegram.index', compact(
            'botConfigured',
            'botUsername',
            'paymentsEnabled',
            'webhookConfigured',
            'stats',
            'payments',
            'phoneCollisions',
            'unverifiedUsers'
        ));
    }

    /**
     * Barcha faol Telegram foydalanuvchilariga ommaviy xabar (Broadcast) yuborish.
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message'     => 'required|string|min:3|max:3500',
            'button_text' => 'nullable|string|max:64',
            'button_url'  => 'nullable|url|max:255',
        ]);

        if (empty(config('telegram.bot_token'))) {
            return back()->with('error', 'TELEGRAM_BOT_TOKEN sozlanmagan. Xabar yuborib bo\'lmaydi.');
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

        $count = 0;
        User::whereNotNull('telegram_id')
            ->whereNull('telegram_blocked_at')
            ->where('is_banned', false)
            ->select(['id', 'telegram_id', 'notification_prefs', 'telegram_blocked_at'])
            ->chunkById(100, function ($users) use ($validated, $options, &$count) {
                foreach ($users as $user) {
                    SendTelegramNotificationJob::dispatch(
                        $user->id,
                        $validated['message'],
                        $options,
                        'broadcast'
                    );
                    $count++;
                }
            });

        return back()->with('success', "Ommaviy xabar {$count} ta faol Telegram foydalanuvchisiga yuborildi!");
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
