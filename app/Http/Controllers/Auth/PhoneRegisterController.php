<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhoneRegisterController extends Controller
{
    /**
     * Ro'yxatdan o'tish so'rovi (POST /register).
     * Telefon raqamni darhol ro'yxatdan o'tkazmaydi, balki Telegram orqali tasdiqlash sahifasiga yo'naltiradi.
     */
    public function register(Request $request): RedirectResponse
    {
        if (!setting('registration_open', true)) {
            return back()->withErrors([
                'phone' => 'Hozirda yangi foydalanuvchilarni ro\'yxatdan o\'tkazish ma\'muriyat tomonidan vaqtincha to\'xtatilgan.',
            ])->withInput();
        }

        $rawPhone = (string) $request->input('phone');
        $normalizedPhone = PhoneNumber::normalize($rawPhone);

        if (!$normalizedPhone || !PhoneNumber::isValid($rawPhone)) {
            return back()->withErrors([
                'phone' => 'Noto\'g\'ri telefon raqami formati. Masalan: +998901234567 yoki 90 123 45 67',
            ])->withInput();
        }

        // 1. Ushbu telefon raqami allaqachon tasdiqlangan foydalanuvchida bormi?
        $existingUser = User::where('phone', $normalizedPhone)->first();
        if ($existingUser && $existingUser->hasVerifiedPhone()) {
            return back()->withErrors([
                'phone' => 'Ushbu telefon raqami allaqachon ro\'yxatdan o\'tgan. Iltimos, hisobingizga kiring.',
            ])->withInput();
        }

        // 2. Validatsiya (Username takrorlanmasligi qat'iy tekshiriladi, Ism esa takrorlanishi mumkin)
        $rawUsername = strtolower(trim((string) $request->input('username')));
        $request->merge(['username' => $rawUsername]);

        if (User::whereRaw('LOWER(username) = ?', [$rawUsername])->exists()) {
            return back()->withErrors([
                'username' => "Ushbu foydalanuvchi nomi (@{$rawUsername}) allaqachon band. Iltimos, boshqa username tanlang.",
            ])->withInput();
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique(User::class, 'username'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'email'    => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
        ], [
            'username.unique'     => "Ushbu foydalanuvchi nomi (@{$rawUsername}) allaqachon band. Iltimos, boshqa username tanlang.",
            'username.alpha_dash' => "Foydalanuvchi nomida faqat lotin harflari, raqamlar, tire va pastki chiziq bo'lishi mumkin.",
            'username.min'        => "Foydalanuvchi nomi kamida 3 ta belgidan iborat bo'lishi kerak.",
            'username.max'        => "Foydalanuvchi nomi 30 ta belgidan oshmasligi kerak.",
            'email.unique'        => "Ushbu email manzil allaqachon ro'yxatdan o'tgan.",
            'password.confirmed'  => "Kiritilgan parollar bir-biriga mos kelmadi.",
        ]);

        // 3. Tasdiqlash tokenini yaratish va keshda vaqtinchalik saqlash (15 daqiqa)
        $regToken = Str::random(32);

        Cache::put("pending_reg_{$regToken}", [
            'name'       => $request->input('name'),
            'username'   => strtolower($request->input('username')),
            'phone'      => $normalizedPhone,
            'email'      => $request->input('email') ?: null,
            'password'   => Hash::make($request->input('password')),
            'ip'         => $request->ip(),
            'created_at' => now(),
        ], now()->addMinutes(15));

        Cache::put("pending_reg_token_by_phone_{$normalizedPhone}", $regToken, now()->addMinutes(15));

        return redirect()->route('register.verify', ['token' => $regToken]);
    }

    /**
     * Telefon raqamni Telegram orqali tasdiqlash sahifasi (GET /register/verify/{token}).
     */
    public function showVerify(string $token): View|RedirectResponse
    {
        $pending = Cache::get("pending_reg_{$token}");

        if (!$pending) {
            return redirect()->route('register')->withErrors([
                'phone' => 'Ro\'yxatdan o\'tish sessiyasi muddati o\'tgan. Iltimos, qaytadan urinib ko\'ring.',
            ]);
        }

        $botUsername = config('telegram.bot_username');
        $tgUrl = "https://t.me/{$botUsername}?start=reg_{$token}";
        $tgAppUrl = "tg://resolve?domain={$botUsername}&start=reg_{$token}";
        $formattedPhone = PhoneNumber::mask($pending['phone']);

        return view('auth.register-verify-phone', compact('token', 'pending', 'botUsername', 'tgUrl', 'tgAppUrl', 'formattedPhone'));
    }

    /**
     * Telegram orqali tasdiqlanganligini polling orqali tekshirish (GET /register/check/{token}).
     */
    public function checkVerify(Request $request, string $token): JsonResponse
    {
        $userId = Cache::get("pending_reg_completed_{$token}");

        if ($userId) {
            $user = User::find($userId);

            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();

                Cache::forget("pending_reg_completed_{$token}");
                Cache::forget("pending_reg_{$token}");

                return response()->json([
                    'confirmed'    => true,
                    'redirect_url' => $user->hasCompletedOnboarding() ? route('home') : route('onboarding'),
                    'message'      => 'Hisobingiz muvaffaqiyatli tasdiqlandi!',
                ]);
            }
        }

        if (!Cache::has("pending_reg_{$token}")) {
            return response()->json([
                'confirmed' => false,
                'expired'   => true,
            ]);
        }

        return response()->json([
            'confirmed' => false,
        ]);
    }
}
