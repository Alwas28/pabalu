<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthEvents
{
    public function handleLogin(Login $event): void
    {
        ActivityLog::create([
            'user_id'     => $event->user->getKey(),
            'action'      => 'login',
            'description' => "User \"{$event->user->name}\" masuk ke sistem.",
            'ip'          => request()->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (!$event->user) return;

        ActivityLog::create([
            'user_id'     => $event->user->getKey(),
            'action'      => 'logout',
            'description' => "User \"{$event->user->name}\" keluar dari sistem.",
            'ip'          => request()->ip(),
        ]);
    }
}
