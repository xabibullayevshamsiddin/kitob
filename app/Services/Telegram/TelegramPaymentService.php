<?php

namespace App\Services\Telegram;

use App\Models\CoinTransaction;
use App\Models\TelegramStarPayment;
use App\Models\User;
use App\Services\Telegram\Exceptions\TelegramApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramPaymentService
{
    protected ?TelegramClient $client;

    /**
     * @param TelegramClient|null $client
     */
    public function __construct($client = null)
    {
        $this->client = $client instanceof TelegramClient ? $client : null;
    }

    /**
     * TelegramClient ob'ektini faqat API chaqirilganda lazy yaratish.
     * Bu token sozlanmagan muhitlarda /shop sahifasi yiqilishining oldini oladi.
     */
    protected function client(): TelegramClient
    {
        if ($this->client === null) {
            $this->client = new TelegramClient();
        }

        return $this->client;
    }

    /**
     * Telegram Stars to'lovlari tizimi yoqilgan yoki yo'qligi (Master Switch).
     */
    public function isPaymentsEnabled(): bool
    {
        return (bool) config('telegram.payments_enabled', false);
    }

    /**
     * Mavjud Telegram Stars tanga paketlari.
     */
    public function getCoinPacks(): array
    {
        return [
            'pack_50' => [
                'id'          => 'pack_50',
                'title'       => '50 ta Oltin tanga',
                'description' => 'Testlarni takrorlash va kichik sovrinlar uchun',
                'coins'       => 50,
                'stars'       => 25,
                'badge'       => 'Boshlang\'ich',
            ],
            'pack_150' => [
                'id'          => 'pack_150',
                'title'       => '150 ta Oltin tanga',
                'description' => 'Eng ommabop to\'plam: avatar hoshiyalari va streak muzlatgich',
                'coins'       => 150,
                'stars'       => 65,
                'badge'       => 'Ommabop',
            ],
            'pack_500' => [
                'id'          => 'pack_500',
                'title'       => '500 ta Oltin tanga',
                'description' => 'Katta to\'plam: barcha eksklyuziv mavzular va kuchaytirgichlar',
                'coins'       => 500,
                'stars'       => 190,
                'badge'       => 'Foydali (+10%)',
            ],
            'pack_1200' => [
                'id'          => 'pack_1200',
                'title'       => '1200 ta Oltin tanga',
                'description' => 'Maksimal to\'plam: VIP unvonlar va doimiy imtiyozlar',
                'coins'       => 1200,
                'stars'       => 420,
                'badge'       => 'Super (+20%)',
            ],
        ];
    }

    /**
     * Tanga paketi uchun Telegram Stars invoice havolasini yaratish.
     */
    public function createCoinPackInvoice(User $user, string $packId): array
    {
        if (!$this->isPaymentsEnabled()) {
            return [
                'success' => false,
                'error'   => 'payments_disabled',
                'message' => 'Telegram Stars orqali to\'lovlar hozirda faol emas.',
            ];
        }

        if (!$user->isTelegramLinked()) {
            return [
                'success' => false,
                'error'   => 'telegram_not_linked',
                'message' => 'To\'lov qilish uchun avval Telegram hisobingizni ulang.',
            ];
        }

        $packs = $this->getCoinPacks();
        $pack = $packs[$packId] ?? null;

        if (!$pack) {
            return [
                'success' => false,
                'error'   => 'pack_not_found',
                'message' => 'Tanga paketi topilmadi.',
            ];
        }

        $uniquePayload = 'star_coin_' . $packId . '_' . $user->id . '_' . time() . '_' . Str::random(8);

        // 1. Pending to'lov qaydini saqlash
        $starPayment = TelegramStarPayment::create([
            'user_id'       => $user->id,
            'payload'       => $uniquePayload,
            'title'         => $pack['title'],
            'stars_amount'  => $pack['stars'],
            'coins_awarded' => $pack['coins'],
            'status'        => 'pending',
        ]);

        // 2. Telegram Bot API createInvoiceLink chaqirish
        try {
            $invoiceLink = $this->client()->createInvoiceLink(
                $pack['title'],
                $pack['description'],
                $uniquePayload,
                'XTR',
                [
                    ['label' => $pack['title'], 'amount' => $pack['stars']]
                ]
            );

            return [
                'success'      => true,
                'invoice_link' => $invoiceLink,
                'payment_id'   => $starPayment->id,
                'payload'      => $uniquePayload,
                'stars'        => $pack['stars'],
                'coins'        => $pack['coins'],
            ];
        } catch (\Throwable $e) {
            $starPayment->update(['status' => 'failed']);

            Log::error("Telegram createInvoiceLink xatosi: " . $e->getMessage(), [
                'user_id' => $user->id,
                'pack_id' => $packId,
            ]);

            return [
                'success' => false,
                'error'   => 'telegram_api_error',
                'message' => 'Telegram to\'lov havolasini shakllantirishda xatolik yuz berdi.',
            ];
        }
    }

    /**
     * pre_checkout_query so'rovini tekshirish va tasdiqlash (10 soniya ichida).
     */
    public function handlePreCheckoutQuery(array $query): bool
    {
        $queryId = (string) ($query['id'] ?? '');
        $payload = (string) ($query['invoice_payload'] ?? '');
        $totalAmount = (int) ($query['total_amount'] ?? 0);

        if (!$this->isPaymentsEnabled()) {
            $this->client()->answerPreCheckoutQuery($queryId, false, 'Telegram Stars to\'lovlari vaqtincha to\'xtatilgan.');
            return false;
        }

        $payment = TelegramStarPayment::where('payload', $payload)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            $this->client()->answerPreCheckoutQuery($queryId, false, 'Buyurtma topilmadi yoki muddati o\'tgan.');
            return false;
        }

        if ($payment->stars_amount !== $totalAmount) {
            $this->client()->answerPreCheckoutQuery($queryId, false, 'To\'lov miqdori mos kelmadi.');
            return false;
        }

        // To'lovni tasdiqlash
        $this->client()->answerPreCheckoutQuery($queryId, true);
        return true;
    }

    /**
     * Muvaffaqiyatli to'lovni qayta ishlash (successful_payment).
     */
    public function handleSuccessfulPayment(array $message): bool
    {
        $successfulPayment = $message['successful_payment'] ?? null;
        if (!$successfulPayment) {
            return false;
        }

        $payload = (string) ($successfulPayment['invoice_payload'] ?? '');
        $chargeId = (string) ($successfulPayment['telegram_payment_charge_id'] ?? '');
        $providerChargeId = $successfulPayment['provider_payment_charge_id'] ?? null;
        $totalAmount = (int) ($successfulPayment['total_amount'] ?? 0);

        // 1. Takroriy tranzaksiya himoyasi (Idempotency)
        if (!empty($chargeId)) {
            $alreadyProcessed = TelegramStarPayment::where('telegram_payment_charge_id', $chargeId)
                ->where('status', 'completed')
                ->first();

            if ($alreadyProcessed) {
                Log::info("Telegram Stars to'lovi allaqachon qayta ishlangan: Charge #{$chargeId}");
                return true;
            }
        }

        $starPayment = TelegramStarPayment::where('payload', $payload)->first();

        if (!$starPayment) {
            Log::error("Telegram Stars to'lovi topilmadi: Payload {$payload}");
            return false;
        }

        // 2. Tranzaksiyada tangalarni hisobga qo'shish va to'lovni yakunlash
        return DB::transaction(function () use ($starPayment, $chargeId, $providerChargeId, $successfulPayment) {
            $user = User::lockForUpdate()->find($starPayment->user_id);

            if (!$user) {
                return false;
            }

            // Tangalarni balansga qo'shish
            $user->coin_balance += $starPayment->coins_awarded;
            $user->save();

            // To'lov holatini completed qilish
            $starPayment->update([
                'status'                      => 'completed',
                'telegram_payment_charge_id'  => $chargeId,
                'provider_payment_charge_id'  => $providerChargeId,
                'raw_payload'                 => $successfulPayment,
            ]);

            // CoinTransaction qaydini yaratish
            CoinTransaction::create([
                'user_id'        => $user->id,
                'coins'          => $starPayment->coins_awarded,
                'source'         => 'telegram_stars',
                'description'    => "Telegram Stars xaridi ({$starPayment->stars_amount} ⭐️): {$starPayment->title}",
                'reference_type' => TelegramStarPayment::class,
                'reference_id'   => $starPayment->id,
                'balance_after'  => $user->coin_balance,
            ]);

            // 3. Foydalanuvchiga Telegram bot orqali chek/xabar yuborish
            if ($user->telegram_id) {
                $coins = number_format($starPayment->coins_awarded);
                $newBalance = number_format($user->coin_balance);

                $text = "⭐️ <b>To'lovingiz muvaffaqiyatli qabul qilindi!</b>\n\n"
                      . "Sizning hisobingizga <b>{$coins} 🪙 oltin tanga</b> qo'shildi.\n"
                      . "• Tranzaksiya: <code>{$chargeId}</code>\n"
                      . "• Sarflangan yulduzlar: <b>{$starPayment->stars_amount} ⭐️</b>\n"
                      . "• Joriy tanga balansingiz: <b>{$newBalance} 🪙</b>\n\n"
                      . "Platformadagi do'kon bo'limidan sara imtiyozlarni xarid qilishingiz mumkin! 🛍";

                try {
                    $this->client()->sendMessage($user->telegram_id, $text);
                } catch (\Throwable $e) {
                    Log::warning("Telegram Stars chek xabarini yuborishda xato: " . $e->getMessage());
                }
            }

            Log::info("Telegram Stars to'lovi yakunlandi: User #{$user->id}, +{$starPayment->coins_awarded} tanga.");
            return true;
        });
    }

    /**
     * Telegram Stars to'lovini bekor qilish va qaytarish (Refund).
     */
    public function refundPayment(TelegramStarPayment $payment, ?string $reason = null): array
    {
        if (!$payment->isCompleted()) {
            return [
                'success' => false,
                'error'   => 'not_completed',
                'message' => 'Faqat yakunlangan to\'lovlarni qaytarish mumkin.',
            ];
        }

        if ($payment->isRefunded()) {
            return [
                'success' => false,
                'error'   => 'already_refunded',
                'message' => 'Ushbu to\'lov allaqachon qaytarilgan.',
            ];
        }

        $user = $payment->user;
        if (!$user || !$user->telegram_id || empty($payment->telegram_payment_charge_id)) {
            return [
                'success' => false,
                'error'   => 'missing_charge_id',
                'message' => 'Foydalanuvchi Telegram ID yoki tranzaksiya ID mavjud emas.',
            ];
        }

        // 1. Telegram API refundStarPayment chaqirish
        try {
            $this->client()->refundStarPayment($user->telegram_id, $payment->telegram_payment_charge_id);
        } catch (TelegramApiException $e) {
            Log::error("Telegram refundStarPayment xatosi: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => 'api_error',
                'message' => 'Telegram Stars to\'lovini qaytarishda xatolik: ' . $e->getMessage(),
            ];
        }

        // 2. DB'da tangalarni qaytarib olish va statusni yangilash
        return DB::transaction(function () use ($payment, $user, $reason) {
            $lockedUser = User::lockForUpdate()->find($user->id);

            // Tangalarni kamaytirish (manfiy bo'lib ketmasligi uchun max(0, ...))
            $deductCoins = min($payment->coins_awarded, $lockedUser->coin_balance);
            $lockedUser->coin_balance -= $deductCoins;
            $lockedUser->save();

            $payment->update([
                'status'      => 'refunded',
                'refunded_at' => now(),
            ]);

            // CoinTransaction qaydini yaratish (manfiy)
            if ($deductCoins > 0) {
                CoinTransaction::create([
                    'user_id'        => $lockedUser->id,
                    'coins'          => -$deductCoins,
                    'source'         => 'telegram_stars_refund',
                    'description'    => "Telegram Stars to'lovini qaytarish (#{$payment->telegram_payment_charge_id})" . ($reason ? ": {$reason}" : ''),
                    'reference_type' => TelegramStarPayment::class,
                    'reference_id'   => $payment->id,
                    'balance_after'  => $lockedUser->coin_balance,
                ]);
            }

            Log::info("Telegram Stars to'lovi muvaffaqiyatli qaytarildi: Payment #{$payment->id}, -{$deductCoins} tanga.");

            return [
                'success' => true,
                'message' => 'Telegram Stars to\'lovi muvaffaqiyatli qaytarildi.',
            ];
        });
    }

    /**
     * getStarTransactions orqali to'langan, lekin webhook yetib kelmagan (pending)
     * to'lovlarni kunlik solishtirish va tiklash (Reconciliation).
     */
    public function reconcileStarTransactions(int $limit = 100): array
    {
        try {
            $response = $this->client()->getStarTransactions(0, $limit);
        } catch (\Throwable $e) {
            Log::error('Telegram getStarTransactions xatosi: ' . $e->getMessage());
            return [
                'success'   => false,
                'fulfilled' => 0,
                'checked'   => 0,
                'message'   => $e->getMessage(),
            ];
        }

        $transactions = $response['result']['transactions'] ?? [];
        $fulfilled = 0;
        $checked = 0;

        foreach ($transactions as $tx) {
            $checked++;
            $chargeId = (string) ($tx['id'] ?? '');
            $amount = (int) ($tx['amount'] ?? 0);
            $source = $tx['source'] ?? [];
            $invoicePayload = (string) ($source['invoice_payload'] ?? '');

            // Faqat kiruvchi user to'lovlari va invoice_payload bor tranzaksiyalarni tekshiramiz
            if ($amount <= 0 || empty($invoicePayload) || empty($chargeId)) {
                continue;
            }

            $pendingPayment = TelegramStarPayment::where('payload', $invoicePayload)
                ->where('status', 'pending')
                ->first();

            if ($pendingPayment && $pendingPayment->stars_amount === $amount) {
                $ok = $this->handleSuccessfulPayment([
                    'successful_payment' => [
                        'currency'                   => 'XTR',
                        'total_amount'               => $amount,
                        'invoice_payload'            => $invoicePayload,
                        'telegram_payment_charge_id' => $chargeId,
                        'reconciled_at'              => now()->toIso8601String(),
                    ],
                ]);

                if ($ok) {
                    $fulfilled++;
                }
            }
        }

        return [
            'success'   => true,
            'checked'   => $checked,
            'fulfilled' => $fulfilled,
        ];
    }
}
