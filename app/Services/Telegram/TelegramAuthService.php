<?php

namespace App\Services\Telegram;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramAuthService
{
    /**
     * Telegram Login Widget tomonidan yuborilgan ma'lumotlar haqiqiyligini tekshirish (HMAC-SHA256).
     * Rasmiy hujjat: https://core.telegram.org/widgets/login
     */
    public function verifyWidgetData(array $data): bool
    {
        if (empty($data['hash']) || empty($data['auth_date'])) {
            return false;
        }

        // 1. auth_date 24 soatdan (86400 soniya) eski bo'lmasligi kerak
        if (abs(time() - (int) $data['auth_date']) > 86400) {
            return false;
        }

        $checkHash = (string) $data['hash'];
        unset($data['hash']);

        // 2. Barcha kalit-qiymatlarni alfavit bo'yicha saralash
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $dataCheckArr[] = $key . '=' . $value;
            }
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        // 3. Bot tokenining SHA256 hashi maxfiy kalit sifatida ishlatiladi
        $botToken = (string) config('telegram.bot_token', '');
        if (empty($botToken)) {
            return false;
        }

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // 4. Doimiy vaqtli (timing-safe) taqqoslash
        return hash_equals($calculatedHash, $checkHash);
    }

    /**
     * Telegram Mini App (TMA) tomonidan yuborilgan initData haqiqiyligini tekshirish (HMAC-SHA256).
     * Rasmiy hujjat: https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     *
     * @param string $initData
     * @return array|false
     */
    public function verifyInitData(string $initData): array|false
    {
        if (trim($initData) === '') {
            return false;
        }

        // 1. Query stringni parse qilish
        parse_str($initData, $params);

        if (empty($params['hash']) || empty($params['auth_date'])) {
            return false;
        }

        // 2. auth_date 24 soatdan (86400 soniya) eski bo'lmasligi kerak
        if (abs(time() - (int) $params['auth_date']) > 86400) {
            return false;
        }

        $checkHash = (string) $params['hash'];
        unset($params['hash']);

        // 3. Alfavit bo'yicha saralash: key=value
        $dataCheckArr = [];
        foreach ($params as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        // 4. HMAC kalitini hisoblash: secret_key = HMAC_SHA256("WebAppData", bot_token)
        $botToken = (string) config('telegram.bot_token', '');
        if (empty($botToken)) {
            return false;
        }

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // 5. Timing-safe taqqoslash
        if (!hash_equals($calculatedHash, $checkHash)) {
            return false;
        }

        // user JSON obyektini parse qilish
        if (isset($params['user']) && is_string($params['user'])) {
            $params['user_data'] = json_decode($params['user'], true);
        }

        return $params;
    }

    /**
     * Telegram ma'lumotlari orqali foydalanuvchini topish yoki yangi foydalanuvchi yaratish.
     */
    public function findOrCreateUser(array $telegramData): array
    {
        $telegramId = (int) ($telegramData['id'] ?? 0);
        if ($telegramId <= 0) {
            return [
                'success' => false,
                'error'   => 'invalid_id',
                'message' => "Telegram ID noto'g'ri.",
            ];
        }

        // 1. Mavjud foydalanuvchini telegram_id bo'yicha qidirish
        $user = User::where('telegram_id', $telegramId)->first();

        if ($user) {
            // Bloklanganligini tekshirish
            if ($user->is_banned) {
                $reason = $user->ban_reason ? ": {$user->ban_reason}" : '';
                return [
                    'success' => false,
                    'error'   => 'banned',
                    'message' => "Hisobingiz ma'muriyat tomonidan bloklangan{$reason}.",
                ];
            }

            // Ma'lumotlarni (username, bloklanganlik belgisi) yangilab qo'yamiz
            $updates = [];
            if (!empty($telegramData['username']) && $user->telegram_username !== $telegramData['username']) {
                $updates['telegram_username'] = $telegramData['username'];
            }
            if ($user->telegram_blocked_at !== null) {
                $updates['telegram_blocked_at'] = null; // Botga qaytgan
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            return [
                'success' => true,
                'user'    => $user,
                'is_new'  => false,
            ];
        }

        // 2. Yangi foydalanuvchi yaratish
        $phone = null;
        if (!empty($telegramData['phone_number'])) {
            $phone = PhoneNumber::normalize($telegramData['phone_number']);
        }

        // Agar telefon raqam berilgan bo'lsa va u allaqachon boshqa hisobda mavjud bo'lsa
        if ($phone && User::where('phone', $phone)->exists()) {
            return [
                'success' => false,
                'error'   => 'phone_exists',
                'message' => "Ushbu telefon raqami ({$phone}) boshqa hisobga ulangan. Iltimos, o'sha hisobingizga kiring yoki yordamga murojaat qiling.",
            ];
        }

        // Noyob username yaratish
        $username = $this->generateUniqueUsername($telegramData['username'] ?? null, $telegramId);

        // To'liq ism
        $name = trim(($telegramData['first_name'] ?? '') . ' ' . ($telegramData['last_name'] ?? ''));
        if (empty($name)) {
            $name = 'Kitobxon ' . substr((string) $telegramId, -4);
        }

        $welcomeCoins = (int) setting('welcome_bonus_coins', 50);

        $user = User::create([
            'name'                => $name,
            'username'            => strtolower($username),
            'email'               => null,
            'password'            => null,
            'telegram_id'         => $telegramId,
            'telegram_username'   => $telegramData['username'] ?? null,
            'telegram_linked_at'  => now(),
            'telegram_blocked_at' => null,
            'phone'               => $phone,
            'phone_verified_at'   => $phone ? now() : null,
            'avatar'              => $telegramData['photo_url'] ?? null,
            'role'                => 'reader',
            'total_points'        => 0,
            'coin_balance'        => $welcomeCoins,
        ]);

        // Yangi hisob uchun profil, streak va bonuslarni o'rnatish
        if ($welcomeCoins > 0) {
            try {
                CoinTransaction::create([
                    'user_id'     => $user->id,
                    'coins'       => $welcomeCoins,
                    'source'      => 'bonus',
                    'description' => 'Ro\'yxatdan o\'tish uchun xush kelibsiz bonusi 🎁',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Telegram registratsiyada coin transaction yaratilmadi: ' . $e->getMessage());
            }
        }

        try {
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('reader');
            }
        } catch (\Throwable $e) {}

        try {
            UserProfile::create([
                'user_id'          => $user->id,
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]);
        } catch (\Throwable $e) {}

        try {
            UserStreak::create([
                'user_id'          => $user->id,
                'current_streak'   => 0,
                'longest_streak'   => 0,
                'last_active_date' => null,
            ]);
        } catch (\Throwable $e) {}

        return [
            'success' => true,
            'user'    => $user,
            'is_new'  => true,
        ];
    }

    /**
     * Mavjud tizimga kirgan foydalanuvchiga Telegram hisobini bog'lash.
     */
    public function linkTelegram(User $user, array $telegramData): array
    {
        $telegramId = (int) ($telegramData['id'] ?? 0);
        if ($telegramId <= 0) {
            return [
                'success' => false,
                'message' => "Noto'g'ri Telegram ID.",
            ];
        }

        // Boshqa foydalanuvchida bu telegram_id bormi?
        $existing = User::where('telegram_id', $telegramId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => "Ushbu Telegram hisobi allaqachon boshqa akkauntga ulangan.",
            ];
        }

        $updates = [
            'telegram_id'         => $telegramId,
            'telegram_username'   => $telegramData['username'] ?? null,
            'telegram_linked_at'  => now(),
            'telegram_blocked_at' => null,
        ];

        // Agar foydalanuvchida telefon bo'lmasa va Telegram'dan telefon kelsa
        if (empty($user->phone) && !empty($telegramData['phone_number'])) {
            $normPhone = PhoneNumber::normalize($telegramData['phone_number']);
            if ($normPhone && !User::where('phone', $normPhone)->where('id', '!=', $user->id)->exists()) {
                $updates['phone'] = $normPhone;
                $updates['phone_verified_at'] = now();
            }
        }

        $user->update($updates);

        return [
            'success' => true,
            'message' => "Telegram hisobingiz muvaffaqiyatli ulandi!",
        ];
    }

    /**
     * Telegram hisobini uzish (unlink).
     */
    public function unlinkTelegram(User $user): array
    {
        // Foydalanuvchida boshqa kirish usuli (parol) bo'lishi shart
        if (!$user->hasPassword()) {
            return [
                'success' => false,
                'message' => "Telegram hisobini uzishdan oldin profilingizga parol o'rnating, aks holda hisobingizga qayta kira olmaysiz.",
            ];
        }

        $user->update([
            'telegram_id'         => null,
            'telegram_username'   => null,
            'telegram_linked_at'  => null,
            'telegram_blocked_at' => null,
        ]);

        return [
            'success' => true,
            'message' => "Telegram hisobi muvaffaqiyatli uzildi.",
        ];
    }

    /**
     * Telefon raqamini Telegram bot orqali tasdiqlash uchun bir martalik havola generatsiya qilish.
     */
    public function generatePhoneVerificationUrl(User $user): string
    {
        $token = Str::random(32);
        Cache::put("tg_verify_token_{$token}", $user->id, now()->addMinutes(30));

        $botUsername = (string) config('telegram.bot_username', 'kitobxonS_bot');
        return "https://t.me/{$botUsername}?start=verify_{$token}";
    }

    /**
     * Noyob username hosil qilish.
     */
    protected function generateUniqueUsername(?string $telegramUsername, int $telegramId): string
    {
        if (!empty($telegramUsername)) {
            $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $telegramUsername);
            if (!empty($clean) && !User::where('username', strtolower($clean))->exists()) {
                return strtolower($clean);
            }
        }

        $base = 'id' . $telegramId;
        if (!User::where('username', $base)->exists()) {
            return $base;
        }

        return 'user_' . substr(md5((string) $telegramId . microtime()), 0, 8);
    }
}
