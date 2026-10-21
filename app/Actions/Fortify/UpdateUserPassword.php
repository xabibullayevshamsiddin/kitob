<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'password' => $this->passwordRules(),
        ];

        // Foydalanuvchida avval parol bo'lsa, joriy parolni kiritish talab qilinadi.
        // Agar Telegram orqali ro'yxatdan o'tgan bo'lsa va paroli bo'lmasa, joriy parol so'ralmaydi.
        if ($user->hasPassword()) {
            $rules['current_password'] = ['required', 'string', 'current_password:web'];
        }

        Validator::make($input, $rules, [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
