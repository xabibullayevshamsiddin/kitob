<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Sayt tilini boshqarish: ?lang=ru / uz / en so'rov orqali o'zgartiriladi
 * va sessiyada saqlanadi. Asosiy til — o'zbekcha (uz).
 * DB'dan kelayotgan ma'lumotlar (kitob nomlari va h.k.) tilga bog'liq emas.
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

        // 2) Sessiyadagi tilni qo'llaymiz (yo'q bo'lsa — o'zbekcha)
        $locale = Session::get('locale', config('app.locale', 'uz'));

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'uz';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
