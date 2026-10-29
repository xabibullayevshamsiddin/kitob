<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Parolni tiklash so'rovini qabul qilish (Telefon yoki Email orqali).
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $input = trim((string) ($request->input('email') ?: $request->input('phone', '')));

        if (empty($input)) {
            throw ValidationException::withMessages([
                'email' => 'Iltimos, telefon raqamingiz yoki emailingizni kiriting.',
            ]);
        }

        // 1. Agar telefon raqami kiritilgan bo'lsa
        $normalizedPhone = PhoneNumber::normalize($input);
        $user = null;

        if ($normalizedPhone) {
            $user = User::where('phone', $normalizedPhone)->first();
        }

        // 2. Agar telefon bo'yicha topilmasa, email yoki username bo'yicha qidiramiz
        if (!$user) {
            $user = User::where('email', $input)
                ->orWhere('username', strtolower($input))
                ->first();
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => "Ushbu ma'lumotlarga ega foydalanuvchi hisobi topilmadi.",
            ]);
        }

        // 3. Agar foydalanuvchida Telegram ulangan bo'lsa -> Telegram orqali havola yuborish (1 daqiqalik qulf bilan)
        if ($user->isTelegramLinked() && !$user->isTelegramBlocked()) {
            $cooldownKey = 'tg_pwd_reset_cooldown_' . $user->id;
            $availableAt = (int) \Illuminate\Support\Facades\Cache::get($cooldownKey, 0);
            $remaining = max(0, $availableAt - now()->timestamp);

            if ($remaining > 0) {
                return back()
                    ->with('cooldown', $remaining)
                    ->withErrors([
                        'email' => "🔒 Iltimos, parolni tiklash so'rovini qayta yuborish uchun {$remaining} soniya kuting.",
                    ]);
            }

            try {
                // Parol brokeri uchun vaqtinchalik identifikator
                $tempEmail = $user->email ?? ($user->phone . '@phone.local');
                if (!$user->email) {
                    $user->email = $tempEmail;
                    $user->saveQuietly();
                }

                $token = Password::broker()->createToken($user);

                $publicBase = config('app.url', 'https://kitobxon.elitehost.uz');
                if (str_contains($publicBase, 'localhost') || str_contains($publicBase, '127.0.0.1')) {
                    $publicBase = 'https://kitobxon.elitehost.uz';
                }
                $publicResetUrl = rtrim($publicBase, '/') . '/reset-password/' . $token . '?email=' . urlencode($user->email);
                $localResetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

                $telegram = new TelegramClient();
                $message = "<b>Parolni yangilash so'rovi 🔐</b>\n\n"
                    . "Assalomu alaykum, <b>" . htmlspecialchars($user->name) . "</b>!\n\n"
                    . "Platformada parolingizni tiklash yoki yangi parol o'rnatish uchun quyidagi tugmani bosing yoki havolaga kiring:\n\n"
                    . "👉 <a href=\"{$publicResetUrl}\">{$publicResetUrl}</a>\n\n";

                if (app()->environment('local', 'testing')) {
                    $message .= "💻 <b>Lokal kompyuter (localhost) havolasi:</b>\n<code>{$localResetUrl}</code>\n\n";
                }

                $message .= "<i>Agar bu so'rovni siz yubormagan bo'lsangiz, xabarni e'tiborsiz qoldiring. Havola 60 daqiqa amal qiladi.</i>";

                $telegram->sendMessage($user->telegram_id, $message, [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔑 Yangi parol o\'rnatish', 'url' => $publicResetUrl]
                            ]
                        ]
                    ]
                ]);

                \Illuminate\Support\Facades\Cache::put($cooldownKey, now()->addSeconds(60)->timestamp, now()->addSeconds(60));

                return back()
                    ->with('cooldown', 60)
                    ->with('status', "Telegram hisobingizga parolni yangilash havolasi yuborildi! Iltimos, Telegram botingizni tekshiring.");
            } catch (\Throwable $e) {
                Log::warning('Telegram orqali parol tiklashda xatolik: ' . $e->getMessage());
            }
        }

        // 4. Agar foydalanuvchida email bo'lsa -> standart email xabarnomasi
        if ($user->hasEmail()) {
            $status = Password::broker()->sendResetLink(['email' => $user->email]);

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', __($status));
            }

            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        // 5. Agar foydalanuvchida email yo'q va Telegram bot ulanmagan bo'lsa
        $botUsername = (string) config('telegram.bot_username', 'Davra_books_bot');
        return back()->withErrors([
            'email' => "Hisobingizga email ulanmagan. Iltimos, Telegram (@{$botUsername}) orqali kiring yoki yordamga murojaat qiling.",
        ]);
    }
}
