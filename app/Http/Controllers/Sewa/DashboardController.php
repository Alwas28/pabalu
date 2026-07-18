<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Outlet $outlet): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        $outlet->load('outletType', 'owner');

        return view('sewa.show', compact('outlet', 'user'));
    }
}
