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

        if ($user && !$user->isAdminOrTeacher() && !$user->hasCompletedOnboarding()) {
            if (
                !$request->routeIs('onboarding', 'logout', 'pwa.ping', 'contact', 'contact.send', 'terms', 'privacy', 'faq')
                && !$request->is('livewire/*', 'telegram/*', 'auth/telegram/*', 'api/*')
                && !$request->expectsJson()
            ) {
                return redirect()->route('onboarding');
            }
        }

        return $next($request);
    }
}
