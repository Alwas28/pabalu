<?php

namespace App\Providers;

use App\Listeners\LogAuthEvents;
use App\Models\BillingInvoice;
use App\Models\Setting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
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

            // Bagikan billingInvoice aktif ke layout (hanya untuk owner yang login)
            if (Auth::check() && Auth::user()->isOwner()) {
                $view->with('billingInvoice', BillingInvoice::activeFor(Auth::id()));
            } elseif (! $view->offsetExists('billingInvoice')) {
                $view->with('billingInvoice', null);
            }
        });
    }
}
