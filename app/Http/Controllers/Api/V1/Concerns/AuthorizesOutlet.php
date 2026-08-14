<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait AuthorizesOutlet
{
    private function authorizeOutlet(Request $request, Outlet $outlet): User
    {
        /** @var User $user */
        $user = $request->user();

        if (!$outlet->isAccessibleBy($user)) {
            throw new HttpException(403, 'Anda tidak memiliki akses ke outlet ini.');
        }

        return $user;
    }
}
