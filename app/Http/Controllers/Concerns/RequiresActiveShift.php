<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Outlet;
use App\Models\User;

trait RequiresActiveShift
{
    /**
     * Returns true if the outlet requires an active shift from this user
     * but none is currently open. The caller should block the action when true.
     */
    private function shiftRequired(Outlet $outlet, User $user): bool
    {
        if (!$outlet->enable_opening_shift) {
            return false;
        }

        return $outlet->shifts()
            ->where('user_id', $user->id)
            ->whereNull('closed_at')
            ->doesntExist();
    }
}
