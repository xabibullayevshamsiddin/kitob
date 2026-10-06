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

        // 1. Agar foydalanuvchining bugungi streaki allaqachon hisoblangan bo'lsa:
        if ($user->streak && $user->streak->last_active_date && $user->streak->last_active_date->toDateString() === $today) {
            session(['streak_checked_date' => $today]);
            return $next($request);
        }

        if ($this->countAnyVisit) {
            // "Har kuni kirish" qoidasi: istalgan tashrif streak beradi.
            app(StreakService::class)->recordActivity($user);
            session(['streak_checked_date' => $today]);
        } else {
            // "O'qish faoliyati" qoidasi:
            // Agar bugun o'qish sessiyasi yoki minutes_read > 0 bo'lsa, streakni yangilaymiz.
            $hasTodayReading = $user->readingSessions()->where('session_date', $today)->exists()
                || $user->dailyActivities()->where('activity_date', $today)->where('minutes_read', '>', 0)->exists();

            if ($hasTodayReading) {
                app(StreakService::class)->recordActivity($user);
                session(['streak_checked_date' => $today]);
            }
            // Agar hali o'qimagan bo'lsa, sessionni qulflamaymiz — keyinroq o'qiganida hisoblanishi uchun
        }

        return $next($request);
    }
}
