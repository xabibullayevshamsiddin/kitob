<?php

namespace App\Services\Telegram;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

        // 1. auth_date 1 soatdan (3600 soniya) eski bo'lmasligi kerak
        $maxAge = (int) config('telegram.auth_max_age', 3600);
        if (abs(time() - (int) $data['auth_date']) > $maxAge) {
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

        // 2. auth_date 1 soatdan (3600 soniya) eski bo'lmasligi kerak
        $maxAge = (int) config('telegram.auth_max_age', 3600);
        if (abs(time() - (int) $params['auth_date']) > $maxAge) {
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
     * Telegram OpenID Connect (OIDC) authorization code'ni token va foydalanuvchi ma'lumotlariga almashtirish.
     * PKCE (code_verifier) va nonce tekshiruvini qo'llab-quvvatlaydi.
     */
    public function exchangeOidcCode(
        string $code,
        string $redirectUri,
        ?string $codeVerifier = null,
        ?string $expectedNonce = null
    ): array|false {
        $clientId = (string) config('telegram.login_client_id', '');
        $clientSecret = (string) config('telegram.login_client_secret', '');

        if (empty($clientId)) {
            return false;
        }

        $payload = [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $redirectUri,
            'client_id'    => $clientId,
        ];

        if (!empty($clientSecret)) {
            $payload['client_secret'] = $clientSecret;
        }

        if (!empty($codeVerifier)) {
            $payload['code_verifier'] = $codeVerifier;
        }

        try {
            $tokenEndpoint = (string) config('telegram.oidc_token_url', 'https://oauth.telegram.org/token');
            $response = Http::asForm()->timeout(15)->post($tokenEndpoint, $payload);

            if (!$response->successful()) {
                return false;
            }

            $tokenData = $response->json();
            $idToken = $tokenData['id_token'] ?? null;

            if (!is_string($idToken) || substr_count($idToken, '.') !== 2) {
                return false;
            }

            [, $bodyB64, ] = explode('.', $idToken);
            $claims = json_decode($this->base64UrlDecode($bodyB64), true);

            if (!is_array($claims)) {
                return false;
            }

            // Standart OIDC claimlarni tekshirish
            $iss = (string) ($claims['iss'] ?? '');
            if ($iss !== '' && !str_contains($iss, 'oauth.telegram.org')) {
                return false;
            }

            if (isset($claims['aud']) && (string) $claims['aud'] !== $clientId) {
                return false;
            }

            if (isset($claims['exp']) && (int) $claims['exp'] < time()) {
                return false;
            }

            if ($expectedNonce !== null && isset($claims['nonce']) && !hash_equals($expectedNonce, (string) $claims['nonce'])) {
                return false;
            }

            $telegramId = (int) ($claims['id'] ?? $claims['sub'] ?? 0);
            if ($telegramId <= 0) {
                return false;
            }

            $fullName = trim((string) ($claims['name'] ?? ''));
            $firstName = (string) ($claims['given_name'] ?? $claims['first_name'] ?? $fullName);
            $lastName = (string) ($claims['family_name'] ?? $claims['last_name'] ?? '');

            return [
                'id'           => $telegramId,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'username'     => $claims['preferred_username'] ?? $claims['username'] ?? null,
                'phone_number' => $claims['phone_number'] ?? null,
                'photo_url'    => $claims['picture'] ?? $claims['photo_url'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Telegram OIDC code almashtirishda xatolik yuz berdi.');
            return false;
        }
    }

    /**
     * Telegram ma'lumotlari orqali foydalanuvchini topish yoki yangi foydalanuvchi yaratish.
     * MUHIM: 50 tanga xush kelibsiz bonusi faqat noyob telefon raqami tasdiqlangandagina beriladi!
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

        $incomingPhone = null;
        if (!empty($telegramData['phone_number'])) {
            $incomingPhone = PhoneNumber::normalize($telegramData['phone_number']);
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

            $updates = [];
            if (!empty($telegramData['username']) && $user->telegram_username !== $telegramData['username']) {
                $updates['telegram_username'] = $telegramData['username'];
            }
            if ($user->telegram_blocked_at !== null) {
                $updates['telegram_blocked_at'] = null;
            }
            if (empty($user->phone) && $incomingPhone && !User::where('phone', $incomingPhone)->where('id', '!=', $user->id)->exists()) {
                $updates['phone'] = $incomingPhone;
                $updates['phone_verified_at'] = now();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            if ($user->hasVerifiedPhone()) {
                $this->awardWelcomeBonusIfEligible($user);
            }

            return [
                'success'              => true,
                'user'                 => $user->fresh(),
                'is_new'               => false,
                'needs_phone_verification' => !$user->hasVerifiedPhone(),
            ];
        }

        // 2. Yangi foydalanuvchi yaratish
        // Agar telefon raqam berilgan bo'lsa va u allaqachon boshqa hisobda mavjud bo'lsa
        if ($incomingPhone && User::where('phone', $incomingPhone)->exists()) {
            return [
                'success' => false,
                'error'   => 'phone_exists',
                'message' => "Ushbu telefon raqami ({$incomingPhone}) boshqa hisobga ulangan. Iltimos, o'sha hisobingizga kiring yoki yordamga murojaat qiling.",
            ];
        }

        // Noyob username yaratish
        $username = $this->generateUniqueUsername($telegramData['username'] ?? null, $telegramId);

        // To'liq ism
        $name = trim(($telegramData['first_name'] ?? '') . ' ' . ($telegramData['last_name'] ?? ''));
        if (empty($name)) {
            $name = 'Kitobxon ' . substr((string) $telegramId, -4);
        }

        // Telefon tasdiqlanmaguncha xush kelibsiz bonusi berilmaydi (multi-akkaunt tanga yig'ishdan himoya)
        $hasVerifiedPhone = !empty($incomingPhone);

        $user = User::create([
            'name'                => $name,
            'username'            => strtolower($username),
            'email'               => null,
            'password'            => null,
            'telegram_id'         => $telegramId,
            'telegram_username'   => $telegramData['username'] ?? null,
            'telegram_linked_at'  => now(),
            'telegram_blocked_at' => null,
            'phone'               => $incomingPhone,
            'phone_verified_at'   => $hasVerifiedPhone ? now() : null,
            'avatar'              => $telegramData['photo_url'] ?? null,
            'role'                => 'reader',
            'total_points'        => 0,
            'coin_balance'        => 0,
        ]);

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

        $bonusAwarded = 0;
        if ($hasVerifiedPhone) {
            $bonusAwarded = $this->awardWelcomeBonusIfEligible($user);
        }

        return [
            'success'                  => true,
            'user'                     => $user->fresh(),
            'is_new'                   => true,
            'bonus_awarded'            => $bonusAwarded,
            'needs_phone_verification' => !$hasVerifiedPhone,
        ];
    }

    /**
     * Foydalanuvchi o'z noyob telefon raqamini tasdiqlaganda bir martalik xush kelibsiz bonusini (50 tanga) berish.
     */
    public function awardWelcomeBonusIfEligible(User $user): int
    {
        if (empty($user->phone) || empty($user->phone_verified_at)) {
            return 0;
        }

        $welcomeCoins = (int) setting('welcome_bonus_coins', 50);
        if ($welcomeCoins <= 0) {
            return 0;
        }

        $alreadyAwarded = CoinTransaction::where('user_id', $user->id)
            ->where('source', 'bonus')
            ->where('description', 'like', '%xush kelibsiz%')
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        $user->increment('coin_balance', $welcomeCoins);

        try {
            CoinTransaction::create([
                'user_id'       => $user->id,
                'coins'         => $welcomeCoins,
                'source'        => 'bonus',
                'description'   => 'Ro\'yxatdan o\'tish uchun xush kelibsiz bonusi 🎁',
                'balance_after' => $user->coin_balance,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram registratsiyada coin transaction yaratilmadi: ' . $e->getMessage());
        }

        return $welcomeCoins;
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

        if ($user->hasVerifiedPhone()) {
            $this->awardWelcomeBonusIfEligible($user);
        }

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
     * User-Agent qatoridan qurilma va brauzer nomini qisqa ko'rinishda ajratib olish.
     */
    public function describeDevice(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Noma\'lum qurilma';
        }

        $browser = 'Brauzer';
        if (str_contains($userAgent, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera')) {
            $browser = 'Opera';
        } elseif (str_contains($userAgent, 'Chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari/')) {
            $browser = 'Safari';
        }

        $os = 'Qurilma';
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Mac OS')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        }

        return "{$browser} ({$os})";
    }

    protected function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($input, '-_', '+/'));
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
