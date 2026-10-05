<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            session()->flash('success', 'Xush kelibsiz, ' . ($event->user->name ?? 'foydalanuvchi') . '! 👋');
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function () {
            session()->flash('info', 'Tizimdan muvaffaqiyatli chiqdingiz.');
        });

        Event::listen(\Illuminate\Auth\Events\Registered::class, function () {
            session()->flash('success', 'Ro\'yxatdan muvaffaqiyatli o\'tdingiz! Xush kelibsiz. 🎉');
        });

        Event::listen(\Illuminate\Auth\Events\Verified::class, function () {
            session()->flash('success', 'Email manzilingiz muvaffaqiyatli tasdiqlandi! ✅');
        });

        Event::listen(\Illuminate\Auth\Events\PasswordReset::class, function () {
            session()->flash('success', 'Parolingiz muvaffaqiyatli yangilandi! 🔐');
        });
    }
}
