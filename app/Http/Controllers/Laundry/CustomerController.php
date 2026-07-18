<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) abort(403);
        return $user;
    }

    public function search(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        $q = trim($request->input('q', ''));

        $customers = Customer::where('outlet_id', $outlet->id)
            ->when($q !== '', fn($query) => $query->where('name', 'like', '%' . $q . '%')
                ->orWhere('phone', 'like', '%' . $q . '%'))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }
}
