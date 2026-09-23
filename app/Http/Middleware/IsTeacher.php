<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasRole(['admin', 'teacher'])) {
            abort(403, 'Sizda o\'qituvchi huquqi yo\'q.');
        }

        return $next($request);
    }
}
