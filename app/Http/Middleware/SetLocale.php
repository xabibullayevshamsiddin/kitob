<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

/**
 * Sayt tilini boshqarish: ?lang=ru / uz / en so'rov orqali o'zgartiriladi
 * va sessiyada, cookie'da hamda foydalanuvchi profilida saqlanadi. Asosiy til — o'zbekcha (uz).
 */
class SetLocale
{
    public const SUPPORTED = ['uz', 'ru', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $selectedLocale = null;

        // 1) URL orqali so'rov bo'lsa (?lang=ru / uz / en) — eng yuqori ustuvorlik
        if ($request->has('lang') && in_array($request->lang, self::SUPPORTED, true)) {
            $selectedLocale = $request->lang;
            Session::put('locale', $selectedLocale);
            Cookie::queue(Cookie::make('locale', $selectedLocale, 60 * 24 * 365));

            // Agar foydalanuvchi tizimga kirgan bo'lsa, profiliga ham doimiy saqlaymiz
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->profile) {
                    $priv = is_array($user->profile->privacy_settings) ? $user->profile->privacy_settings : [];
                    $priv['preferred_locale'] = $selectedLocale;
                    $user->profile->privacy_settings = $priv;
                    $user->profile->save();
                }
            }
        }

        // 2) Agar URL da lang parametri berilmagan bo'lsa:
        if (!$selectedLocale) {
            // A) Foydalanuvchi tizimga kirgan bo'lsa, uning profilidagi preferred_locale eng ustuvor!
            if (Auth::check()) {
                $userLocale = Auth::user()->profile?->privacy_settings['preferred_locale'] ?? null;
                if (is_string($userLocale) && in_array($userLocale, self::SUPPORTED, true)) {
                    $selectedLocale = $userLocale;
                    Session::put('locale', $selectedLocale);
                }
            }

            // B) Agar profilida bo'lmasa, sessiyadan olamiz
            if (!$selectedLocale) {
                $selectedLocale = Session::get('locale');
            }

            // C) Agar sessiyada ham yo'q bo'lsa, cookie'dan olamiz
            if (!$selectedLocale) {
                $selectedLocale = $request->cookie('locale');
            }

            // D) Hech narsa topilmasa, config yoki default 'uz'
            if (!$selectedLocale || !in_array($selectedLocale, self::SUPPORTED, true)) {
                $selectedLocale = config('app.locale', 'uz');
            }
        }

        if (!in_array($selectedLocale, self::SUPPORTED, true)) {
            $selectedLocale = 'uz';
        }

        App::setLocale($selectedLocale);

        return $next($request);
    }
}
