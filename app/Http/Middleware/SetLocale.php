<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Sayt tilini boshqarish: ?lang=ru / uz / en so'rov orqali o'zgartiriladi
 * va sessiyada hamda foydalanuvchi profilida saqlanadi. Asosiy til — o'zbekcha (uz).
 */
class SetLocale
{
    public const SUPPORTED = ['uz', 'ru', 'en'];

    public function handle(Request $request, Closure $next)
    {
        // 1) URL orqali so'rov bo'lsa — sessiyaga saqlaymiz
        if ($request->has('lang') && in_array($request->lang, self::SUPPORTED, true)) {
            Session::put('locale', $request->lang);
        }

        // 2) Sessiyadagi tilni qo'llaymiz; sessiyada yo'q bo'lsa foydalanuvchi profilidan olamiz
        $locale = Session::get('locale');

        if (!$locale && Auth::check()) {
            $userLocale = Auth::user()->profile?->privacy_settings['preferred_locale'] ?? null;
            if (is_string($userLocale) && in_array($userLocale, self::SUPPORTED, true)) {
                $locale = $userLocale;
                Session::put('locale', $locale);
            }
        }

        if (!$locale || !in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale', 'uz');
        }

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'uz';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
