<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    /**
     * Telegram OIDC yoki Login Widget orqali kelgan ma'lumotlarni qabul qilish (Callback).
     */
    public function callback(Request $request, TelegramAuthService $authService): RedirectResponse
    {
        $verifiedData = false;

        // 1A. OpenID Connect (OIDC Authorization Code + PKCE) oqimi
        if ($request->filled('code')) {
            $state = (string) $request->input('state', '');
            $expectedState = (string) $request->session()->pull('oauth_telegram_state', '');
            $codeVerifier = $request->session()->pull('oauth_telegram_code_verifier');
            $expectedNonce = $request->session()->pull('oauth_telegram_nonce');

            if ($state !== '' && $expectedState !== '' && hash_equals($expectedState, $state)) {
                $verifiedData = $authService->exchangeOidcCode(
                    (string) $request->input('code'),
                    url('/auth/telegram/callback'),
                    is_string($codeVerifier) ? $codeVerifier : null,
                    is_string($expectedNonce) ? $expectedNonce : null
                );
            }
        } else {
            // 1B. Klassik Telegram Login Widget (HMAC-SHA256) oqimi
            $rawData = $request->all();
            if ($authService->verifyWidgetData($rawData)) {
                $verifiedData = $rawData;
            }
        }

        if (!is_array($verifiedData)) {
            // Maxfiylik qoidasi: shaxsiy ma'lumotlar (id, first_name, phone) logga yozilmaydi!
            Log::warning('Telegram Login Callback: Yaroqsiz imzo, state yoki muddati o\'tgan so\'rov.', [
                'ip'   => $request->ip(),
                'flow' => $request->filled('code') ? 'oidc' : 'widget',
            ]);

            return redirect()->route('login')->withErrors([
                'email' => "Telegram orqali kirish tasdiqlanmadi (yaroqsiz ma'lumot yoki muddati o'tgan).",
            ]);
        }

        // 2. Agar foydalanuvchi allaqachon tizimga kirgan bo'lsa -> Telegram hisobini bog'lash (Linking)
        if (Auth::check()) {
            $user = Auth::user();
            $linkResult = $authService->linkTelegram($user, $verifiedData);

            if ($linkResult['success']) {
                return redirect()->route('settings')->with('status', $linkResult['message']);
            }

            return redirect()->route('settings')->withErrors([
                'telegram' => $linkResult['message'],
            ]);
        }

        // 3. Mehmon foydalanuvchi: Kirish yoki yangi ro'yxatdan o'tish
        $result = $authService->findOrCreateUser($verifiedData);

        if (!$result['success']) {
            return redirect()->route('login')->withErrors([
                'email' => $result['message'],
            ]);
        }

        $user = $result['user'];

        // Tizimga kiritish va sessiyani yangilash
        Auth::login($user, true);
        $request->session()->regenerate();

        if ($result['is_new'] ?? false) {
            if (($result['bonus_awarded'] ?? 0) > 0) {
                return redirect()->route('home')->with('status', "Xush kelibsiz! Telegram orqali ro'yxatdan o'tdingiz. 🎁 {$result['bonus_awarded']} tanga bonusingiz hisobingizga qo'shildi!");
            }

            return redirect()->route('home')->with('status', "Xush kelibsiz! Telegram orqali kirdingiz. 🎁 50 tanga xush kelibsiz bonusini olish uchun Sozlamalar bo'limidan telefon raqamingizni tasdiqlang!");
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Telegram OIDC (PKCE + state + nonce) yoki xavfsiz Bot Deep-Link orqali yo'naltirish.
     */
    public function redirect(Request $request, TelegramAuthService $authService): RedirectResponse
    {
        $clientId = config('telegram.login_client_id');
        $botUsername = config('telegram.bot_username');

        // 1. Agar OIDC Client ID sozlangan bo'lsa -> PKCE + state + nonce bilan yo'naltirish
        if (!empty($clientId)) {
            $state = Str::random(40);
            $nonce = Str::random(40);
            $codeVerifier = Str::random(64);
            $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

            $request->session()->put([
                'oauth_telegram_state'         => $state,
                'oauth_telegram_nonce'         => $nonce,
                'oauth_telegram_code_verifier' => $codeVerifier,
            ]);

            $redirectUri = url('/auth/telegram/callback');
            $query = http_build_query([
                'client_id'             => $clientId,
                'redirect_uri'          => $redirectUri,
                'response_type'         => 'code',
                'scope'                 => 'openid profile phone telegram:bot_access',
                'state'                 => $state,
                'nonce'                 => $nonce,
                'code_challenge'        => $codeChallenge,
                'code_challenge_method' => 'S256',
            ]);

            return redirect()->away("https://oauth.telegram.org/auth?{$query}");
        }

        // 2. Agar bot username mavjud bo'lsa -> Brauzerga bog'langan token va 4 xonali tasdiqlash kodi bilan kutish sahifasiga
        if (!empty($botUsername)) {
            $token = Str::random(32);
            $browserSecret = Str::random(40);
            $securityCode = (string) random_int(1000, 9999);

            $request->session()->put("tg_auth_secret_{$token}", $browserSecret);

            Cache::put("tg_auth_login_{$token}", [
                'created_at'     => now()->toIso8601String(),
                'ip'             => $request->ip(),
                'device'         => $authService->describeDevice($request->userAgent()),
                'code'           => $securityCode,
                'browser_secret' => $browserSecret,
            ], now()->addMinutes(15));

            return redirect()->route('auth.telegram.waiting', ['token' => $token]);
        }

        return redirect()->route('login');
    }

    /**
     * Telegram orqali tasdiqlashni kutish sahifasi.
     */
    public function waiting(Request $request, string $token)
    {
        $loginData = Cache::get("tg_auth_login_{$token}");
        if (!$loginData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Kirish sessiyasi eskirgan. Iltimos, qaytadan urinib ko\'ring.',
            ]);
        }

        $botUsername = config('telegram.bot_username');
        $tgUrl = "https://t.me/{$botUsername}?start=auth_{$token}";
        $tgAppUrl = "tg://resolve?domain={$botUsername}&start=auth_{$token}";
        $securityCode = is_array($loginData) ? ($loginData['code'] ?? null) : null;

        return view('auth.telegram-waiting', compact('token', 'botUsername', 'tgUrl', 'tgAppUrl', 'securityCode'));
    }

    /**
     * Telegram tasdiqlanganligini polling orqali tekshirish (AJAX).
     */
    public function check(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        if (Cache::has("tg_auth_rejected_{$token}")) {
            Cache::forget("tg_auth_rejected_{$token}");
            Cache::forget("tg_auth_login_{$token}");

            return response()->json([
                'confirmed' => false,
                'rejected'  => true,
                'message'   => 'Kirish so\'rovi Telegram bot orqali bekor qilindi.',
            ]);
        }

        $loginData = Cache::get("tg_auth_login_{$token}");

        // Brauzer bog'lanishi (Session Fixation / Phishing himoyasi):
        // Agar token ma'lum bir brauzer sessiyasida yaratilgan bo'lsa, faqat o'sha brauzer sessiyasi tekshira oladi
        if (is_array($loginData) && !empty($loginData['browser_secret'])) {
            $sessionSecret = (string) $request->session()->get("tg_auth_secret_{$token}", '');
            if ($sessionSecret === '' || !hash_equals((string) $loginData['browser_secret'], $sessionSecret)) {
                return response()->json([
                    'confirmed' => false,
                    'expired'   => true,
                    'message'   => 'Brauzer sessiyasi mos kelmadi.',
                ], 403);
            }
        }

        $userId = Cache::get("tg_auth_confirmed_{$token}");

        if ($userId) {
            $user = \App\Models\User::find($userId);

            if ($user) {
                if ($user->is_banned) {
                    Cache::forget("tg_auth_confirmed_{$token}");
                    Cache::forget("tg_auth_login_{$token}");

                    return response()->json([
                        'confirmed' => false,
                        'banned'    => true,
                        'message'   => 'Hisobingiz ma\'muriyat tomonidan bloklangan.',
                    ]);
                }

                Auth::login($user, true);
                $request->session()->regenerate();

                Cache::forget("tg_auth_confirmed_{$token}");
                Cache::forget("tg_auth_login_{$token}");

                return response()->json([
                    'confirmed'    => true,
                    'redirect_url' => route('home'),
                ]);
            }
        }

        if (!$loginData) {
            return response()->json([
                'confirmed' => false,
                'expired'   => true,
            ]);
        }

        return response()->json([
            'confirmed' => false,
        ]);
    }

    /**
     * Telegram botidagi tugmadan to'g'ridan-to'g'ri saytga kirish (Deep-link complete).
     */
    public function complete(Request $request, string $token): RedirectResponse
    {
        $loginData = Cache::get("tg_auth_login_{$token}");

        // Agar loginData ichida browser_secret bo'lsa, u faqat o'sha brauzer sessiyasida yoki imzolangan secret bilan ishlaydi
        if (is_array($loginData) && !empty($loginData['browser_secret'])) {
            $sessionSecret = (string) $request->session()->get("tg_auth_secret_{$token}", '');
            $querySecret = (string) $request->query('s', '');
            $expected = (string) $loginData['browser_secret'];

            $validSession = $sessionSecret !== '' && hash_equals($expected, $sessionSecret);
            $validQuery = $querySecret !== '' && hash_equals($expected, $querySecret);

            if (!$validSession && !$validQuery) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Xavfsizlik xatosi: kirish havolasi boshqa qurilmada ochilgan.',
                ]);
            }
        }

        $userId = Cache::get("tg_auth_confirmed_{$token}");

        if ($userId) {
            $user = \App\Models\User::find($userId);

            if ($user) {
                if ($user->is_banned) {
                    Cache::forget("tg_auth_confirmed_{$token}");
                    Cache::forget("tg_auth_login_{$token}");

                    return redirect()->route('login')->withErrors([
                        'email' => 'Hisobingiz ma\'muriyat tomonidan bloklangan.',
                    ]);
                }

                Auth::login($user, true);
                $request->session()->regenerate();

                Cache::forget("tg_auth_confirmed_{$token}");
                Cache::forget("tg_auth_login_{$token}");

                return redirect()->route('home')->with('status', 'Xush kelibsiz! Telegram orqali muvaffaqiyatli kirdingiz. 🎉');
            }
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Kirish havolasi muddati o\'tgan yoki allaqachon foydalanilgan.',
        ]);
    }

    /**
     * Telegram hisobini uzish (Unlink).
     */
    public function unlink(Request $request, TelegramAuthService $authService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $result = $authService->unlinkTelegram($user);

        if ($result['success']) {
            return redirect()->route('settings')->with('status', $result['message']);
        }

        return redirect()->route('settings')->withErrors([
            'telegram' => $result['message'],
        ]);
    }

    /**
     * Telegram bot orqali telefon raqamni tasdiqlash havolasi.
     */
    public function verifyPhone(Request $request, TelegramAuthService $authService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $url = $authService->generatePhoneVerificationUrl($user);
        return redirect()->away($url);
    }

    /**
     * Foydalanuvchining Telegram bildirishnoma sozlamalarini yangilash.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $prefs = [
            'streak_reminder' => $request->boolean('streak_reminder'),
            'daily_quote'     => $request->boolean('daily_quote'),
            'coin_reward'     => $request->boolean('coin_reward'),
            'security'        => $request->boolean('security', true),
        ];

        $user->update([
            'notification_prefs' => $prefs,
        ]);

        return redirect()->route('settings')->with('status', 'Telegram bildirishnoma sozlamalari yangilandi!');
    }
}
