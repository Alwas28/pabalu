<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireOwnerSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'owner' && is_null($user->setup_completed_at)) {
            return redirect()->route('setup.index')
                ->with('info', 'Harap selesaikan pengaturan akun terlebih dahulu.');
        }

        return $next($request);
    }
}
