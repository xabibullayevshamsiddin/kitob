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

        // Agar o'chirilgan (soft-deleted) foydalanuvchilarda ushbu telefon bo'lsa, tozalaymiz
        User::onlyTrashed()
            ->where('phone', $normalizedPhone)
            ->forceDelete();

        // 2. Validatsiya (Username takrorlanmasligi qat'iy tekshiriladi, Ism esa takrorlanishi mumkin)
        $rawUsername = strtolower(trim((string) $request->input('username')));
        $request->merge(['username' => $rawUsername]);

        // Agar o'chirilgan (soft-deleted) foydalanuvchilarda ushbu username bo'lsa, tozalaymiz
        User::onlyTrashed()
            ->whereRaw('LOWER(username) = ?', [$rawUsername])
            ->forceDelete();

        if ($request->filled('email')) {
            User::onlyTrashed()
                ->where('email', $request->input('email'))
                ->forceDelete();
        }

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

        // Localhost muhitida Telegram tasdiqlashni aylanib o'tish (to'g'ridan-to'g'ri hisob ochish)
        $isLocal = app()->environment('local')
            || in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'])
            || str_ends_with($request->getHost(), '.test')
            || str_ends_with($request->getHost(), '.local');

        if ($isLocal) {
            $welcomeCoins = (int) setting('welcome_coins', 50);

            $user = User::create([
                'name'              => $request->input('name'),
                'username'          => $rawUsername,
                'phone'             => $normalizedPhone,
                'email'             => $request->input('email') ?: null,
                'password'          => Hash::make($request->input('password')),
                'phone_verified_at' => now(),
                'role'              => 'reader',
                'total_points'      => 0,
                'coin_balance'      => $welcomeCoins,
            ]);

            if ($welcomeCoins > 0) {
                try {
                    \App\Models\CoinTransaction::create([
                        'user_id'     => $user->id,
                        'coins'       => $welcomeCoins,
                        'source'      => 'bonus',
                        'description' => "Ro'yxatdan o'tish uchun xush kelibsiz bonusi 🎁",
                    ]);
                } catch (\Throwable $e) {}
            }

            try {
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('reader');
                }
            } catch (\Throwable $e) {}

            \App\Models\UserProfile::create([
                'user_id'          => $user->id,
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]);

            \App\Models\UserStreak::create([
                'user_id'          => $user->id,
                'current_streak'   => 0,
                'longest_streak'   => 0,
                'last_active_date' => null,
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->route('onboarding')->with('success', "Xush kelibsiz, {$user->name}! Ro'yxatdan o'tish muvaffaqiyatli yakunlandi.");
        }

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

        $isLocal = app()->environment('local')
            || in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'])
            || str_ends_with(request()->getHost(), '.test')
            || str_ends_with(request()->getHost(), '.local');

        if ($isLocal && $pending) {
            $welcomeCoins = (int) setting('welcome_coins', 50);
            $user = User::create([
                'name'              => $pending['name'],
                'username'          => $pending['username'],
                'phone'             => $pending['phone'],
                'email'             => $pending['email'] ?? null,
                'password'          => $pending['password'],
                'phone_verified_at' => now(),
                'role'              => 'reader',
                'total_points'      => 0,
                'coin_balance'      => $welcomeCoins,
            ]);

            if ($welcomeCoins > 0) {
                try {
                    \App\Models\CoinTransaction::create([
                        'user_id'     => $user->id,
                        'coins'       => $welcomeCoins,
                        'source'      => 'bonus',
                        'description' => "Ro'yxatdan o'tish uchun xush kelibsiz bonusi 🎁",
                    ]);
                } catch (\Throwable $e) {}
            }

            try {
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('reader');
                }
            } catch (\Throwable $e) {}

            \App\Models\UserProfile::create([
                'user_id'          => $user->id,
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]);

            \App\Models\UserStreak::create([
                'user_id'          => $user->id,
                'current_streak'   => 0,
                'longest_streak'   => 0,
                'last_active_date' => null,
            ]);

            Cache::forget("pending_reg_{$token}");

            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->route('onboarding')->with('success', "Xush kelibsiz, {$user->name}! Mahalliy muhitda hisobingiz darhol tasdiqlandi.");
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
