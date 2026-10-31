<?php

namespace App\Services\Topup;

use App\Models\CoinTransaction;
use App\Models\Setting;
use App\Models\TopupRequest;
use App\Models\User;
use App\Services\Gamification\PointsService;
use App\Services\NotifyUser;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Telegram\TelegramClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Karta orqali tanga/ball sotib olish oqimi:
 *  1. createRequest  — paket tanlanadi, unikal summa (masalan 10 037 so'm) band qilinadi
 *  2. submitReceipt  — chek yuklanadi va avtomatik tekshiriladi (dublikat, metadata, AI)
 *  3. approve/reject — admin bank ilovasida pulni ko'rib qaror qiladi; tanga/ball atomar beriladi
 */
class TopupService
{
    public const DISK = 'local';

    public function __construct(
        protected ReceiptInspector $inspector,
        protected ReceiptVisionAnalyzer $analyzer,
    ) {
    }

    // ─── Sozlamalar ────────────────────────────────────────────────────────

    public function isEnabled(): bool
    {
        return (bool) setting('topup_enabled', false) && $this->cardDigits() !== '';
    }

    public function cardDigits(): string
    {
        return preg_replace('/\D+/', '', (string) setting('topup_card_number', '')) ?? '';
    }

    public function cardFormatted(): string
    {
        return trim(chunk_split($this->cardDigits(), 4, ' '));
    }

    public function cardHolder(): string
    {
        return (string) setting('topup_card_holder', '');
    }

    public function cardBank(): string
    {
        return (string) setting('topup_card_bank', '');
    }

    public function dailyLimit(): int
    {
        return (int) setting('topup_daily_limit', config('topup.daily_limit', 200000));
    }

    /** Barcha paketlar (admin tahriri bo'lmasa — config'dagi standartlar) */
    public function allPackages(): array
    {
        $stored = setting('topup_packages');
        $packages = is_string($stored) ? json_decode($stored, true) : $stored;

        if (!is_array($packages) || $packages === []) {
            $packages = config('topup.default_packages', []);
        }

        return array_values(array_filter(array_map([$this, 'normalizePackage'], $packages)));
    }

    public function activePackages(): array
    {
        return array_values(array_filter($this->allPackages(), fn ($p) => $p['active']));
    }

    public function savePackages(array $packages): void
    {
        $normalized = array_values(array_filter(array_map([$this, 'normalizePackage'], $packages)));
        Setting::set('topup_packages', json_encode($normalized, JSON_UNESCAPED_UNICODE), 'topup', 'string');
    }

    protected function normalizePackage($package): ?array
    {
        if (!is_array($package)) {
            return null;
        }

        $price = (int) ($package['price'] ?? 0);
        $coins = max(0, (int) ($package['coins'] ?? 0));
        $points = max(0, (int) ($package['points'] ?? 0));
        $title = trim((string) ($package['title'] ?? ''));

        if ($price < 1000 || ($coins === 0 && $points === 0) || $title === '') {
            return null;
        }

        $key = Str::slug((string) ($package['key'] ?? ''), '_') ?: ('pkg_' . substr(md5($title . $price . $coins . $points), 0, 8));

        return [
            'key'    => mb_substr($key, 0, 40),
            'title'  => mb_substr($title, 0, 120),
            'price'  => $price,
            'coins'  => $coins,
            'points' => $points,
            'active' => (bool) ($package['active'] ?? true),
        ];
    }

    // ─── Foydalanuvchi oqimi ───────────────────────────────────────────────

    public function openRequestFor(User $user): ?TopupRequest
    {
        $this->expireStale($user);

        return TopupRequest::where('user_id', $user->id)->open()->latest('id')->first();
    }

    /** Bugun (Toshkent vaqti) to'langan/tekshiruvdagi summa */
    public function spentToday(User $user): int
    {
        $start = now('Asia/Tashkent')->startOfDay()->utc();

        return (int) TopupRequest::where('user_id', $user->id)
            ->whereIn('status', [TopupRequest::STATUS_APPROVED, TopupRequest::STATUS_PENDING_REVIEW, TopupRequest::STATUS_AWAITING_PAYMENT])
            ->where('created_at', '>=', $start)
            ->sum('expected_amount');
    }

    public function createRequest(User $user, string $packageKey): TopupRequest
    {
        if (!$this->isEnabled()) {
            throw new ShopException("Karta orqali to'lov hozircha yoqilmagan.");
        }

        if ($user->isBanned()) {
            throw new ShopException("Bloklangan hisob to'lov qila olmaydi.");
        }

        $throttleKey = 'topup:create:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw new ShopException("Juda ko'p urinish. " . RateLimiter::availableIn($throttleKey) . " soniyadan keyin qayta urinib ko'ring.");
        }
        RateLimiter::hit($throttleKey, 600);

        $package = collect($this->activePackages())->firstWhere('key', $packageKey);
        if (!$package) {
            throw new ShopException('Paket topilmadi.');
        }

        return DB::transaction(function () use ($user, $package) {
            // Bir vaqtda bitta ochiq so'rov (parallel bosishlardan himoya)
            User::whereKey($user->id)->lockForUpdate()->first();

            if ($this->openRequestFor($user)) {
                throw new ShopException("Sizda yakunlanmagan to'lov so'rovi bor. Avval uni yakunlang yoki bekor qiling.");
            }

            if ($this->spentToday($user) + $package['price'] > $this->dailyLimit()) {
                throw new ShopException('Kunlik limit: ' . number_format($this->dailyLimit(), 0, '.', ' ') . " so'm. Ertaga qayta urinib ko'ring.");
            }

            return TopupRequest::create([
                'user_id'         => $user->id,
                'reference'       => $this->uniqueReference(),
                'package_key'     => $package['key'],
                'package_title'   => $package['title'],
                'coins'           => $package['coins'],
                'points'          => $package['points'],
                'base_amount'     => $package['price'],
                'expected_amount' => $this->uniqueAmount($package['price']),
                'card_last4'      => substr($this->cardDigits(), -4),
                'status'          => TopupRequest::STATUS_AWAITING_PAYMENT,
                'expires_at'      => now()->addHours((int) config('topup.request_ttl_hours', 24)),
            ]);
        });
    }

    public function cancel(TopupRequest $topup, User $user): void
    {
        $updated = TopupRequest::whereKey($topup->id)
            ->where('user_id', $user->id)
            ->where('status', TopupRequest::STATUS_AWAITING_PAYMENT)
            ->update(['status' => TopupRequest::STATUS_CANCELLED]);

        if ($updated !== 1) {
            throw new ShopException("Bu so'rovni bekor qilib bo'lmaydi.");
        }
    }

    /**
     * Chekni qabul qiladi, avtomatik tekshiradi va admin navbatiga qo'yadi.
     */
    public function submitReceipt(TopupRequest $topup, User $user, UploadedFile $file, ?string $payerCardLast4 = null, ?string $note = null): TopupRequest
    {
        if ((int) $topup->user_id !== (int) $user->id || !$topup->isAwaitingPayment()) {
            throw new ShopException("Bu so'rovga chek yuklab bo'lmaydi.");
        }

        if ($topup->expires_at && $topup->expires_at->isPast()) {
            $topup->update(['status' => TopupRequest::STATUS_EXPIRED]);
            throw new ShopException("So'rov muddati o'tgan. Yangi so'rov yarating.");
        }

        $throttleKey = 'topup:receipt:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw new ShopException("Juda ko'p urinish. " . RateLimiter::availableIn($throttleKey) . " soniyadan keyin qayta urinib ko'ring.");
        }
        RateLimiter::hit($throttleKey, 600);

        $realPath = (string) $file->getRealPath();
        $imageInfo = @getimagesize($realPath);
        if (!$imageInfo || !in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new ShopException("Chek JPG, PNG yoki WEBP rasm bo'lishi kerak.");
        }

        // 1. Aynan bir xil fayl — darhol rad (bir chekni ikki marta ishlatish)
        $sha = $this->inspector->sha256($realPath);
        $duplicate = TopupRequest::where('receipt_sha256', $sha)->where('id', '!=', $topup->id)->first();
        if ($duplicate) {
            Log::warning("Topup: takroriy chek yuborildi. User #{$user->id}, so'rov #{$topup->reference}, avvalgi #{$duplicate->reference}");
            throw new ShopException("Bu chek avval yuborilgan. Iltimos, shu to'lovning o'z chekini yuklang.");
        }

        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$imageInfo['mime']];
        $path = $file->storeAs('topup-receipts/' . now()->format('Y/m'), $topup->reference . '_' . Str::random(16) . '.' . $extension, self::DISK);

        $topup->forceFill([
            'receipt_path'     => $path,
            'receipt_mime'     => $imageInfo['mime'],
            'receipt_size'     => (int) $file->getSize(),
            'receipt_sha256'   => $sha,
            'receipt_dhash'    => $this->inspector->dhash($realPath),
            'payer_card_last4' => $payerCardLast4 ? substr(preg_replace('/\D+/', '', $payerCardLast4), -4) ?: null : null,
            'user_note'        => $note ? mb_substr(trim($note), 0, 500) : null,
            'submitted_at'     => now(),
            'status'           => TopupRequest::STATUS_PENDING_REVIEW,
        ])->save();

        $this->analyze($topup);
        $this->notifyAdminsAboutNewReceipt($topup->fresh('user'));

        return $topup->fresh();
    }

    /** Avtomatik tekshiruvlarni (qayta) ishga tushirish — admin "Qayta tekshirish" tugmasi ham shuni chaqiradi */
    public function analyze(TopupRequest $topup): void
    {
        $absolute = Storage::disk(self::DISK)->path((string) $topup->receipt_path);
        if (!$topup->receipt_path || !is_file($absolute)) {
            return;
        }

        $ai = null;
        $aiStatus = 'skipped';
        if (config('topup.ai_enabled', true) && $this->analyzer->isAvailable()) {
            $ai = $this->analyzer->analyze($absolute, (string) $topup->receipt_mime, [
                'expected_amount' => $topup->expected_amount,
                'card_last4'      => $topup->card_last4,
                'created_at'      => $topup->created_at?->timezone('Asia/Tashkent')->format('Y-m-d H:i'),
            ]);
            $aiStatus = $ai === null ? 'failed' : 'done';
        }

        // Tranzaksiya raqami inspect'dan oldin saqlanmaydi — aks holda o'zi bilan dublikat bo'lib chiqadi
        $result = $this->inspector->inspect($topup, $absolute, $ai);

        $topup->forceFill([
            'ai_status'                => $aiStatus,
            'ai_result'                => $ai,
            'extracted_amount'         => $ai['amount'] ?? null,
            'extracted_transaction_id' => $ai['transaction_id'] ?? null,
            'checks'                   => $result['checks'],
            'risk_score'               => $result['risk_score'],
        ])->save();
    }

    // ─── Admin qarorlari ───────────────────────────────────────────────────

    public function approve(TopupRequest $topup, User $admin, ?string $note = null): TopupRequest
    {
        $approved = DB::transaction(function () use ($topup, $admin, $note) {
            /** @var TopupRequest $locked */
            $locked = TopupRequest::whereKey($topup->id)->lockForUpdate()->firstOrFail();

            // Ikki marta tasdiqlash (ikki admin bir vaqtda bossa) — faqat bir marta beriladi
            if (!$locked->isPendingReview()) {
                throw new ShopException("Bu so'rov allaqachon ko'rib chiqilgan ({$locked->status_label}).");
            }

            $user = User::whereKey($locked->user_id)->lockForUpdate()->firstOrFail();

            if ($locked->coins > 0) {
                $user->increment('coin_balance', $locked->coins);
                CoinTransaction::create([
                    'user_id'        => $user->id,
                    'coins'          => $locked->coins,
                    'source'         => 'purchase',
                    'description'    => "Karta orqali xarid #{$locked->reference}: {$locked->package_title}",
                    'reference_type' => TopupRequest::class,
                    'reference_id'   => $locked->id,
                    'balance_after'  => (int) $user->fresh()->coin_balance,
                ]);
            }

            if ($locked->points > 0) {
                app(PointsService::class)->awardPoints(
                    $user,
                    $locked->points,
                    'purchase',
                    "Karta orqali xarid #{$locked->reference}: {$locked->package_title}",
                    $locked->id,
                    TopupRequest::class
                );
            }

            $locked->forceFill([
                'status'      => TopupRequest::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_note'  => $note ? mb_substr(trim($note), 0, 2000) : null,
            ])->save();

            return $locked;
        });

        $this->notifyUser(
            $approved,
            'topup_approved',
            "To'lov tasdiqlandi ✅",
            "#{$approved->reference}: hisobingizga {$approved->reward_label} qo'shildi. Rahmat!",
            '✅'
        );

        return $approved;
    }

    public function reject(TopupRequest $topup, User $admin, string $reason, ?string $note = null): TopupRequest
    {
        $updated = TopupRequest::whereKey($topup->id)
            ->where('status', TopupRequest::STATUS_PENDING_REVIEW)
            ->update([
                'status'        => TopupRequest::STATUS_REJECTED,
                'reviewed_by'   => $admin->id,
                'reviewed_at'   => now(),
                'reject_reason' => mb_substr(trim($reason), 0, 500),
                'admin_note'    => $note ? mb_substr(trim($note), 0, 2000) : null,
                'updated_at'    => now(),
            ]);

        if ($updated !== 1) {
            throw new ShopException("Bu so'rov allaqachon ko'rib chiqilgan.");
        }

        $rejected = $topup->fresh();
        $this->notifyUser(
            $rejected,
            'topup_rejected',
            "To'lov rad etildi",
            "#{$rejected->reference}: {$rejected->reject_reason}. Savol bo'lsa, \"Aloqa\" bo'limi orqali yozing.",
            '⚠️'
        );

        return $rejected;
    }

    /** Muddati o'tgan, chek yuklanmagan so'rovlarni yopadi */
    public function expireStale(?User $user = null): int
    {
        return TopupRequest::where('status', TopupRequest::STATUS_AWAITING_PAYMENT)
            ->where('expires_at', '<', now())
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->update(['status' => TopupRequest::STATUS_EXPIRED, 'updated_at' => now()]);
    }

    // ─── Yordamchilar ──────────────────────────────────────────────────────

    protected function uniqueReference(): string
    {
        do {
            $reference = 'KT' . strtoupper(Str::random(6));
        } while (TopupRequest::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Narxga tasodifiy so'm qo'shib, ochiq so'rovlar orasida takrorlanmaydigan summa tanlaydi.
     * Admin bank SMS'idagi aniq summa bo'yicha qaysi foydalanuvchi to'laganini biladi.
     */
    protected function uniqueAmount(int $base): int
    {
        $min = (int) config('topup.amount_offset_min', 1);
        $max = (int) config('topup.amount_offset_max', 99);

        $taken = TopupRequest::open()
            ->whereBetween('expected_amount', [$base + $min, $base + $max * 10])
            ->pluck('expected_amount')
            ->all();

        $free = array_values(array_diff(range($base + $min, $base + $max), $taken));
        if ($free === []) {
            // Juda ko'p parallel so'rov bo'lsa — kengroq oraliq
            $free = array_values(array_diff(range($base + $max + 1, $base + $max * 10), $taken));
        }

        return $free[array_rand($free)];
    }

    protected function notifyUser(TopupRequest $topup, string $type, string $title, string $body, string $icon): void
    {
        $user = $topup->user;
        if (!$user) {
            return;
        }

        NotifyUser::send($user, $type, $title, $body, $icon, route('topup.index', [], false));

        if ($user->telegram_id && !$user->telegram_blocked_at) {
            $this->sendTelegramLater($user->telegram_id, "<b>{$title}</b>\n\n" . e($body));
        }
    }

    protected function notifyAdminsAboutNewReceipt(TopupRequest $topup): void
    {
        $amount = number_format($topup->expected_amount, 0, '.', ' ');
        $body = "{$topup->user?->name}: {$amount} so'm → {$topup->reward_label} (xavf: {$topup->risk_score}/100)";

        User::where(fn ($q) => $q->where('role', 'admin')->orWhereHas('roles', fn ($r) => $r->where('name', 'admin')))
            ->get()
            ->each(fn (User $admin) => NotifyUser::send($admin, 'topup_pending', "Yangi to'lov cheki #{$topup->reference}", $body, '💳', route('admin.topups.show', $topup, false)));

        $chatId = config('telegram.admin_chat_id');
        if ($chatId) {
            $this->sendTelegramLater($chatId, "💳 <b>Yangi to'lov cheki #{$topup->reference}</b>\n" . e($body) . "\n" . route('admin.topups.show', $topup));
        }
    }

    /** Telegram sekin yoki yopiq bo'lsa ham foydalanuvchi kutib qolmasligi uchun javob yuborilgandan keyin jo'natiladi */
    protected function sendTelegramLater(int|string $chatId, string $text): void
    {
        dispatch(function () use ($chatId, $text) {
            try {
                app(TelegramClient::class)->sendMessage($chatId, $text);
            } catch (\Throwable $e) {
                Log::info('Topup: Telegram xabar yuborilmadi — ' . $e->getMessage());
            }
        })->afterResponse();
    }
}
