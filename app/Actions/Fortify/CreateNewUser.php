<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (!setting('registration_open', true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Hozirda yangi foydalanuvchilarni ro\'yxatdan o\'tkazish ma\'muriyat tomonidan vaqtincha to\'xtatilgan.',
            ]);
        }

        if (!config('telegram.auth_email_registration_enabled', true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Elektron pochta orqali yangi ro\'yxatdan o\'tish o\'chirilgan. Iltimos, Telegram orqali ro\'yxatdan o\'ting.',
            ]);
        }

        $rawPhone = $input['phone'] ?? null;
        $normalizedPhone = $rawPhone ? \App\Support\PhoneNumber::normalize($rawPhone) : null;
        $input['phone'] = $normalizedPhone;

        $hasPhone = !empty($rawPhone);
        $hasEmail = !empty($input['email']);

        if (!$hasPhone && !$hasEmail) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone' => 'Iltimos, telefon raqamingizni kiriting.',
            ]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];

        if ($hasPhone) {
            $rules['phone'] = [
                'required',
                'string',
                Rule::unique(User::class, 'phone'),
                function ($attribute, $value, $fail) use ($rawPhone, $normalizedPhone) {
                    if (!$normalizedPhone || !\App\Support\PhoneNumber::isValid($rawPhone)) {
                        $fail("Noto'g'ri telefon raqami formati. Masalan: +998901234567 yoki 90 123 45 67");
                    }
                },
            ];
        }

        if ($hasEmail || !$hasPhone) {
            $rules['email'] = [
                $hasPhone ? 'nullable' : 'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ];
        }

        Validator::make($input, $rules)->validate();

        $unusedRules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique(User::class),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];

        $welcomeCoins = (int) setting('welcome_bonus_coins', 50);

        $user = User::create([
            'name' => $input['name'],
            'username' => strtolower($input['username']),
            'email' => !empty($input['email']) ? $input['email'] : null,
            'phone' => $normalizedPhone,
            'password' => Hash::make($input['password']),
            'email_verified_at' => !empty($input['email']) ? now() : null,
            'phone_verified_at' => null,
            'role' => 'reader',
            'total_points' => 0,
            'coin_balance' => $welcomeCoins,
        ]);

        if ($welcomeCoins > 0) {
            try {
                \App\Models\CoinTransaction::create([
                    'user_id'     => $user->id,
                    'coins'       => $welcomeCoins,
                    'source'      => 'bonus',
                    'description' => 'Ro\'yxatdan o\'tish uchun xush kelibsiz bonusi 🎁',
                ]);
            } catch (\Throwable $e) {}
        }

        try {
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('reader');
            }
        } catch (\Throwable $e) {}

        UserProfile::create([
            'user_id' => $user->id,
            'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
        ]);

        UserStreak::create([
            'user_id' => $user->id,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_active_date' => null,
        ]);

        return $user;
    }
}
