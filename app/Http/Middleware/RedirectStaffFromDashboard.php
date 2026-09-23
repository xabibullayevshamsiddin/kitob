<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Umumiy /dashboard sahifasidan admin va teacherni o'z panellariga yo'naltiradi.
 * Studentlar esa Livewire Dashboard to'liq sahifa komponentini ko'radi.
 */
class RedirectStaffFromDashboard
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user && $user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }

        return $next($request);
    }
}
