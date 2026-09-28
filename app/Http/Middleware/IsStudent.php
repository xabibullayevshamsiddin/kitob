<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Faqat student (va admin) rollariga ruxsat beradi.
 * Teacher o'z paneliga yo'naltiriladi, noma'lum foydalanuvchi login sahifasiga.
 */
class IsStudent
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admin hammasiga kira oladi
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Teacher o'z paneliga yo'naltiriladi
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard')
                ->with('warning', 'Bu sahifa faqat o\'quvchilar uchun. Siz o\'qituvchi paneliga yo\'naltirildinggiz.');
        }

        // Student yoki Reader — ruxsat
        if ($user->hasRole('student') || $user->hasRole('reader') || in_array($user->role, ['student', 'reader', 'user']) || (!$user->hasRole('teacher') && !$user->hasRole('admin'))) {
            return $next($request);
        }

        // Boshqa holatda ham ruxsat (oddiy o'quvchi)
        return $next($request);
    }
}
