<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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

        $totalCustomers = Customer::where('outlet_id', $outlet->id)->count();

        return view('sewa.show', compact('outlet', 'user', 'totalCustomers'));
    }
}
