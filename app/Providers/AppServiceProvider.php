<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Render (va boshqa proxy-based hostlar) uchun HTTPS majburiy qilish
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        \Illuminate\Pagination\Paginator::useTailwind();
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.tailwind');
    }
}
