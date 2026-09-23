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
        Validator::make($input, [
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
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'username' => strtolower($input['username']),
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'reader',
            'total_points' => 0,
            'coin_balance' => 0,
        ]);

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
