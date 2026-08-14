<?php

namespace App\Providers;

use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');

        try {
            $timezone = AppSetting::get('timezone', config('app.timezone'));
            if ($timezone) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Throwable $e) {
            // Tabel app_settings belum ada (mis. sebelum migrasi pertama dijalankan) — abaikan, pakai default config.
        }
    }
}
