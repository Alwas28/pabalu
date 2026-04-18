<?php

namespace App\Providers;

use App\Listeners\LogAuthEvents;
use App\Models\Setting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $listener = new LogAuthEvents();

        Event::listen(Login::class,  [$listener, 'handleLogin']);
        Event::listen(Logout::class, [$listener, 'handleLogout']);

        // Bagikan currency_symbol ke semua view (cached via Setting::get)
        View::composer('*', function ($view) {
            $view->with('currencySymbol', Setting::get('currency_symbol', 'Rp'));
        });
    }
}
