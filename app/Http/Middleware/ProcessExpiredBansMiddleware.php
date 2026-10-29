<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProcessExpiredBansMiddleware
{
    /**
     * Muddati tugagan banlarni avtomatik yechish va Telegram xabarini yuborish.
     * Saytga kelayotgan har qanday so'rov paytida 30 soniyalik kesh lock bilan tekshiriladi.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Agar joriy kirgan user ban olgan bo'lsa va muddati o'tgan bo'lsa - darhol unban
        if ($user = $request->user()) {
            if ($user->is_banned && $user->banned_until && $user->banned_until->isPast()) {
                $user->unban();
            }
        }

        // 2. Tizimdagi barcha muddati o'tgan banlarni har 30 soniyada bir marta tekshirish
        if (Cache::add('process_expired_bans_global_lock', 1, 30)) {
            try {
                User::processExpiredBans();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $next($request);
    }
}
