<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin',
        'admin/*',
        'login',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->maintenanceMode()->active()) {
            $data = $this->app->maintenanceMode()->data();

            // 1. Secret bypass URL (/admin-bypass)
            if (isset($data['secret']) && ($request->path() === $data['secret'] || $request->is($data['secret']))) {
                return $this->bypassResponse($data['secret']);
            }

            // 2. Agar foydalanuvchi Admin bo'lsa — saytning HAR QANDAY sahifasiga kirishga ruxsat!
            if (auth()->check()) {
                $user = auth()->user();
                if (($user->role ?? null) === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
                    return $next($request);
                }
            }

            // 3. Maxsus bypass cookie yoki ruxsat etilgan URLlar (admin/*, login, logout)
            if ($this->hasValidBypassCookie($request, $data) || $this->inExceptArray($request)) {
                return $next($request);
            }

            // 4. Redirect mavjud bo'lsa
            if (isset($data['redirect'])) {
                $path = $data['redirect'] === '/' ? $data['redirect'] : trim($data['redirect'], '/');
                if ($request->path() !== $path) {
                    return redirect($path);
                }
            }

            // 5. 503 andozasini ko'rsatish
            if (view()->exists('errors.503')) {
                return response()->view('errors.503', [], $data['status'] ?? 503, $this->getHeaders($data));
            }

            if (isset($data['template'])) {
                return response($data['template'], $data['status'] ?? 503, $this->getHeaders($data));
            }

            throw new HttpException($data['status'] ?? 503, 'Service Unavailable', null, $this->getHeaders($data));
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     * Subdirectory (OSPanel localhost/Kitob/public) moslashuvi bilan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        $path = $request->path();

        foreach ($this->getExcludedPaths() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except) || $path === $except) {
                return true;
            }

            // Wildcard mosligi
            $wildcard = str_replace('*', '.*', $except);
            if (preg_match('#^' . $wildcard . '$#i', $path)) {
                return true;
            }
        }

        return false;
    }
}
