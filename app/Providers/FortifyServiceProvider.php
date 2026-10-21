<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LogoutResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Auth sahifalariga 'guest' middleware: tizimga kirgan foydalanuvchi
        // login/register/parol tiklash sahifalariga kira olmaydi — bosh sahifaga yo'naltiriladi
        Fortify::loginView(function () {
            if (Auth::check()) {
                return redirect()->route('home');
            }
            return view('auth.login');
        });

        Fortify::registerView(function () {
            if (Auth::check()) {
                return redirect()->route('home');
            }
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            if (Auth::check()) {
                return redirect()->route('home');
            }
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function (Request $request) {
            if (Auth::check()) {
                return redirect()->route('home');
            }
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) ($request->input('email') ?: ($request->input('phone') ?: $request->input('login', '')));
            return Limit::perMinute(5)->by($email . $request->ip());
        });

        Fortify::authenticateUsing(function (Request $request) {
            $login = trim((string) ($request->input('email') ?: ($request->input('phone') ?: $request->input('login', ''))));
            $password = (string) $request->input('password', '');

            if ($login === '' || $password === '') {
                return null;
            }

            // 1. Agar telefon raqami kiritilgan bo'lsa
            $normalizedPhone = \App\Support\PhoneNumber::normalize($login);
            $user = null;

            if ($normalizedPhone) {
                $user = \App\Models\User::where('phone', $normalizedPhone)->first();
            }

            // 2. Agar telefon bo'yicha topilmasa, email yoki username bo'yicha qidiramiz
            if (!$user) {
                $user = \App\Models\User::where('email', $login)
                    ->orWhere('username', strtolower($login))
                    ->first();
            }

            // 3. Parolni tekshirish
            if ($user && !empty($user->password) && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                if ($user->is_banned) {
                    $reason = $user->ban_reason ? ": {$user->ban_reason}" : '';
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => "Hisobingiz ma'muriyat tomonidan bloklangan{$reason}.",
                    ]);
                }

                return $user;
            }

            return null;
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
