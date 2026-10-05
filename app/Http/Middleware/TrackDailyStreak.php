<?php

namespace App\Http\Middleware;

use App\Services\Gamification\StreakService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackDailyStreak
{
    /**
     * Streak (kunlik ketma-ketlik) qoidasi — osongina almashtirish uchun flag.
     *
     * false (standart — hozirgi qoida): streak faqat bugungi o'qish faoliyati
     * (reading_sessions yozuvi yoki daily_activities'da minutes_read > 0)
     * mavjud bo'lganda hisoblanadi.
     *
     * true: kunlik istalgan tashrif (har qanday sahifa ochish) streak
     * hisoblanadi — "har kuni kirgan foydalanuvchiga streak berilsin"
     * talabi bo'lsa shu flagni true qilish kifoya.
     */
    protected bool $countAnyVisit = false;

    /**
     * Avval bu tekshiruv faqat profil sahifasi ochilganda ishlagan, shu sababli
     * foydalanuvchi saytda faol bo'lsa ham streak yangilanmasdi. Endi har qanday
     * sahifa yuklanganda, kuniga bir marta tekshiriladi.
     */
    public function handle(Request $request, Closure $next)
    {
        $today = Carbon::now('Asia/Tashkent')->toDateString();

        // Performans: mehmonlar uchun ishlamaydi; autentifikatsiyadan o'tgan
        // foydalanuvchi uchun kuniga bir marta bazaga murojaat qilamiz.
        if (!Auth::check() || session('streak_checked_date') === $today) {
            return $next($request);
        }

        $user = Auth::user();

        if ($this->countAnyVisit) {
            // "Har kuni kirish" qoidasi: istalgan tashrif streak beradi.
            app(StreakService::class)->recordActivity($user);
        } else {
            // "O'qish faoliyati" qoidasi (ProfilePage::syncUserMetrics() dagi mantiq):
            // bugungi o'qish yozuvi bo'lsa, streakni yangilaymiz. recordActivity()
            // kuniga bir marta hisoblaydi (last_active_date === bugun bo'lsa o'tkazib yuboradi),
            // shuning uchun qayta chaqirish xavfsiz va ketma-ket kunlar to'g'ri oshadi.
            $hasTodayReading = $user->readingSessions()->where('session_date', $today)->exists()
                || $user->dailyActivities()->where('activity_date', $today)->where('minutes_read', '>', 0)->exists();

            if ($hasTodayReading) {
                app(StreakService::class)->recordActivity($user);
            }
        }

        // Bugun endi qayta tekshirmaymiz.
        session(['streak_checked_date' => $today]);

        return $next($request);
    }
}
