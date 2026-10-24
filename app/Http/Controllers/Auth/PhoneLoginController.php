<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\TelegramClient;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PhoneLoginController extends Controller
{
    /**
     * Tizimga kirish so'rovi (POST /login).
     * Telefon va parol to'g'ri bo'lsa, to'g'ridan-to'g'ri kiritmaydi, balki Telegram botiga 6 xonali kod yuboradi!
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        // 1. Telefon raqami bo'yicha qidirish
        $normalizedPhone = PhoneNumber::normalize($loginInput);
        $user = null;

        if ($normalizedPhone) {
            $user = User::where('phone', $normalizedPhone)->first();
        }

        // 2. Agar telefon bo'yicha topilmasa, email yoki username bo'yicha qidirish
        if (!$user) {
            $user = User::where('email', $loginInput)
                ->orWhere('username', strtolower($loginInput))
                ->first();
        }

        // 3. Parolni tekshirish
        if (!$user || empty($user->password) || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Telefon raqam yoki parol noto\'g\'ri.',
            ]);
        }

        // 4. Bloklanganlik tekshiruvi
        if ($user->is_banned) {
            $reason = $user->ban_reason ? ": {$user->ban_reason}" : '';
            throw ValidationException::withMessages([
                'email' => "Hisobingiz ma'muriyat tomonidan bloklangan{$reason}.",
            ]);
        }

        // Localhost muhitida Telegram 2FA kodini aylanib o'tish (to'g'ridan-to'g'ri tizimga kirish)
        $isLocal = app()->environment('local')
            || in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'])
            || str_ends_with($request->getHost(), '.test')
            || str_ends_with($request->getHost(), '.local');

        if ($isLocal) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended($user->hasCompletedOnboarding() ? route('home') : route('onboarding'));
        }

        // 5. 6 xonali tasdiqlash kodini hosil qilish
        $code = (string) random_int(100000, 999999);
        $twoFactorToken = Str::random(32);
        $cooldownSeconds = 60;

        $loginData = [
            'user_id'             => $user->id,
            'code'                => $code,
            'remember'            => $request->boolean('remember'),
            'attempts'            => 0,
            'created_at'          => now(),
            'resend_available_at' => now()->addSeconds($cooldownSeconds)->timestamp,
        ];

        $throttleKey = 'resend_2fa_' . $user->id;
        RateLimiter::hit($throttleKey, $cooldownSeconds);

        // 6. Kodni Telegram bot orqali yuborish
        if ($user->telegram_id) {
            try {
                $client = new TelegramClient();
                $message = "🔐 <b>Kitobxon platformasiga kirish tasdig'i:</b>\n\n"
                         . "Sizning bir martalik 6 xonali tasdiqlash kodingiz:\n"
                         . "👉 <code>{$code}</code> 👈\n\n"
                         . "Ushbu kodni hech kimga bermang! Kod amal qilish muddati: 5 daqiqa.";

                $client->sendMessage($user->telegram_id, $message);
            } catch (\Throwable $e) {
                // Xatolik yuz bersa ham davom etadi
            }
        }

        Cache::put("login_2fa_{$twoFactorToken}", $loginData, now()->addMinutes(5));

        return redirect()->route('login.verify-code', ['token' => $twoFactorToken]);
    }

    /**
     * 6 xonali tasdiqlash kodini kiritish oynasi (GET /login/verify-code/{token}).
     */
    public function showVerifyCode(string $token): View|RedirectResponse
    {
        $loginData = Cache::get("login_2fa_{$token}");

        if (!$loginData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tasdiqlash sessiyasi muddati o\'tgan. Iltimos, qaytadan kiring.',
            ]);
        }

        $user = User::find($loginData['user_id']);
        if (!$user) {
            return redirect()->route('login');
        }

        // Localhost muhitida tasdiqlash sahifasiga kelib qolgan bo'lsa ham avtomatik kiritish
        $isLocal = app()->environment('local')
            || in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'])
            || str_ends_with(request()->getHost(), '.test')
            || str_ends_with(request()->getHost(), '.local');

        if ($isLocal) {
            Auth::login($user, (bool) ($loginData['remember'] ?? false));
            request()->session()->regenerate();
            Cache::forget("login_2fa_{$token}");

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }
            return redirect()->intended($user->hasCompletedOnboarding() ? route('home') : route('onboarding'));
        }

        $botUsername = config('telegram.bot_username');
        $hasTelegram = (bool) $user->telegram_id;
        $maskedPhone = $user->phone ? PhoneNumber::mask($user->phone) : ($user->email ?? 'hisobingiz');
        $tgUrl = "https://t.me/{$botUsername}?start=login_code_{$token}";

        $throttleKey = 'resend_2fa_' . $user->id;
        $rateLimiterRemaining = RateLimiter::tooManyAttempts($throttleKey, 1)
            ? RateLimiter::availableIn($throttleKey)
            : 0;
        $storedRemaining = max(0, (int) (($loginData['resend_available_at'] ?? 0) - now()->timestamp));
        $cooldownSeconds = max($rateLimiterRemaining, $storedRemaining);

        return view('auth.login-verify-code', compact('token', 'user', 'botUsername', 'hasTelegram', 'maskedPhone', 'tgUrl', 'cooldownSeconds'));
    }

    /**
     * 6 xonali kodni tasdiqlash (POST /login/verify-code/{token}).
     */
    public function verifyCode(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $loginData = Cache::get("login_2fa_{$token}");

        if (!$loginData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tasdiqlash muddati o\'tgan (5 daqiqa). Iltimos, qaytadan kiring.',
            ]);
        }

        $attempts = ($loginData['attempts'] ?? 0) + 1;
        $loginData['attempts'] = $attempts;

        if ($attempts > 5) {
            Cache::forget("login_2fa_{$token}");
            return redirect()->route('login')->withErrors([
                'email' => 'Ko\'p marta noto\'g\'ri kod kiritildi. Iltimos, qaytadan kiring.',
            ]);
        }

        $enteredCode = trim((string) $request->input('code'));

        $isLocal = app()->environment('local')
            || in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'])
            || str_ends_with($request->getHost(), '.test')
            || str_ends_with($request->getHost(), '.local');

        if ($enteredCode !== (string) $loginData['code'] && !($isLocal && in_array($enteredCode, ['111111', '000000', '123456']))) {
            Cache::put("login_2fa_{$token}", $loginData, now()->addMinutes(5));
            return back()->withErrors([
                'code' => 'Kiritilgan 6 xonali kod noto\'g\'ri. Telegram botingizni tekshirib, qaytadan kiriting.',
            ]);
        }

        // Muvaffaqiyatli!
        Cache::forget("login_2fa_{$token}");

        $user = User::find($loginData['user_id']);
        if (!$user) {
            return redirect()->route('login');
        }

        Auth::login($user, (bool) ($loginData['remember'] ?? false));
        $request->session()->regenerate();

        if (!$user->hasCompletedOnboarding()) {
            return redirect()->route('onboarding')->with('status', "Xush kelibsiz, {$user->name}! 👋 Keling, shaxsiy mutolaa rejangizni sozlab olamiz.");
        }

        return redirect()->intended(route('home'))->with('status', "Xush kelibsiz, {$user->name}! 👋");
    }

    /**
     * Kodni qayta yuborish (POST /login/resend-code/{token}).
     */
    public function resendCode(Request $request, string $token): RedirectResponse
    {
        $loginData = Cache::get("login_2fa_{$token}");

        if (!$loginData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sessiya eskirgan. Qaytadan kiring.',
            ]);
        }

        $throttleKey = 'resend_2fa_' . $loginData['user_id'];
        $storedRemaining = max(0, (int) (($loginData['resend_available_at'] ?? 0) - now()->timestamp));
        if (RateLimiter::tooManyAttempts($throttleKey, 1) || $storedRemaining > 0) {
            $seconds = max(RateLimiter::availableIn($throttleKey), $storedRemaining, 1);
            return back()->withErrors([
                'code' => "🔒 Kodni qayta yuborish vaqtincha qulflangan. Iltimos, {$seconds} soniya kuting.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $newCode = (string) random_int(100000, 999999);
        $loginData['code'] = $newCode;
        $loginData['attempts'] = 0;
        $loginData['resend_available_at'] = now()->addSeconds(60)->timestamp;
        Cache::put("login_2fa_{$token}", $loginData, now()->addMinutes(5));

        $user = User::find($loginData['user_id']);
        if ($user && $user->telegram_id) {
            try {
                $client = new TelegramClient();
                $message = "🔐 <b>Yangi kirish kodi:</b>\n\n"
                         . "Sizning yangi tasdiqlash kodingiz:\n"
                         . "👉 <code>{$newCode}</code> 👈\n\n"
                         . "Amal qilish muddati: 5 daqiqa.";
                $client->sendMessage($user->telegram_id, $message);
            } catch (\Throwable $e) {}
        }

        return back()->with('status', 'Yangi 6 xonali tasdiqlash kodi Telegram botingizga yuborildi!');
    }
}
