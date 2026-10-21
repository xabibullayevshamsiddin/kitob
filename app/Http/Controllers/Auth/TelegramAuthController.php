<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramAuthController extends Controller
{
    /**
     * Telegram Login Widget orqali kelgan ma'lumotlarni qabul qilish (Callback).
     */
    public function callback(Request $request, TelegramAuthService $authService): RedirectResponse
    {
        $data = $request->all();

        // 1. Telegram ma'lumotlari haqiqiyligini tekshirish
        if (!$authService->verifyWidgetData($data)) {
            Log::warning('Telegram Login Callback: Yaroqsiz yoki soxta imzo.', [
                'ip'   => $request->ip(),
                'data' => $data,
            ]);

            return redirect()->route('login')->withErrors([
                'email' => "Telegram orqali kirish tasdiqlanmadi (yaroqsiz ma'lumot yoki muddati o'tgan).",
            ]);
        }

        // 2. Agar foydalanuvchi allaqachon tizimga kirgan bo'lsa -> Telegram hisobini bog'lash (Linking)
        if (Auth::check()) {
            $user = Auth::user();
            $linkResult = $authService->linkTelegram($user, $data);

            if ($linkResult['success']) {
                return redirect()->route('settings')->with('status', $linkResult['message']);
            }

            return redirect()->route('settings')->withErrors([
                'telegram' => $linkResult['message'],
            ]);
        }

        // 3. Mehmon foydalanuvchi: Kirish yoki yangi ro'yxatdan o'tish
        $result = $authService->findOrCreateUser($data);

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
            return redirect()->route('home')->with('status', "Xush kelibsiz! Telegram orqali ro'yxatdan o'tdingiz. 🎁 50 tanga bonusingiz hisobingizga qo'shildi!");
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Telegram OIDC / Redirect (agar alohida sahifadan yo'naltirilsa).
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('telegram.login_client_id');
        $botUsername = config('telegram.bot_username');

        if (!empty($clientId)) {
            $redirectUri = url('/auth/telegram/callback');
            $url = "https://oauth.telegram.org/auth?client_id={$clientId}&redirect_uri=" . urlencode($redirectUri) . "&response_type=code&scope=openid+profile+phone+telegram:bot_access";
            return redirect()->away($url);
        }

        // 2. Agar bot username mavjud bo'lsa, Telegram botiga deep-link orqali yo'naltirish
        if (!empty($botUsername)) {
            $token = \Illuminate\Support\Str::random(32);
            \Illuminate\Support\Facades\Cache::put("tg_auth_login_{$token}", [
                'created_at' => now(),
                'ip'         => $request->ip(),
            ], now()->addMinutes(15));

            return redirect()->away("https://t.me/{$botUsername}?start=auth_{$token}");
        }

        return redirect()->route('login');
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
