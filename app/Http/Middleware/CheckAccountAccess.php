<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) return $next($request);

        // Admin selalu lolos
        if ($user->isAdmin()) return $next($request);

        // Tentukan owner yang relevan
        $owner = $user->ownerAccount();

        if (!$owner) return $next($request);

        // Akun dinonaktifkan admin
        if ($owner->account_type === 'inactive') {
            return redirect()->route('account.suspended', ['reason' => 'inactive']);
        }

        // Trial habis
        if ($owner->isTrialExpired()) {
            return redirect()->route('account.suspended', ['reason' => 'expired']);
        }

        return $next($request);
    }
}
