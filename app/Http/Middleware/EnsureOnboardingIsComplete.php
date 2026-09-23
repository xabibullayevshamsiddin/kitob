<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOnboardingIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && (!$user->profile || empty($user->profile->reading_place))) {
            if (!$request->routeIs('onboarding') && !$request->routeIs('logout')) {
                return redirect()->route('onboarding');
            }
        }

        return $next($request);
    }
}
