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
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }

        $this->app->singleton(\App\Services\AI\BookAssistant\ProviderManager::class, function () {
            return new \App\Services\AI\BookAssistant\ProviderManager();
        });
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

        \App\Models\BookChapter::observe(\App\Observers\BookChapterObserver::class);

        // Dinamik platforma sozlamalarini yuklash
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                if ($appName = \App\Models\Setting::get('app_name')) {
                    config(['app.name' => $appName]);
                }
                if ($timezone = \App\Models\Setting::get('timezone')) {
                    config(['app.timezone' => $timezone]);
                }
                if ($contactEmail = \App\Models\Setting::get('contact_email')) {
                    config(['mail.from.address' => $contactEmail]);
                }
            }
        } catch (\Throwable $e) {
            // Migratsiyalar paytida yoki bazaga ulanish yo'q paytda sokin o'tadi
        }
    }
}
