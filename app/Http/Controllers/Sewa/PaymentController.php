<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }
        return $user;
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);
        $from = $data['from'] ?? null;
        $to   = $data['to'] ?? null;

        $query = RentalPayment::whereHas('rentalTransaction', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->with('rentalTransaction.customer', 'rentalTransaction.rentalUnit.rentalItem');

        if ($from) $query->whereDate('paid_at', '>=', $from);
        if ($to)   $query->whereDate('paid_at', '<=', $to);

        $payments = (clone $query)->orderByDesc('paid_at')->paginate(25)->withQueryString();
        $totalFiltered = (clone $query)->sum('amount');

        $totalToday = RentalPayment::whereHas('rentalTransaction', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');

        return view('sewa.payments.index', compact('outlet', 'user', 'payments', 'totalToday', 'totalFiltered', 'from', 'to'));
    }

    public function show(Outlet $outlet, RentalPayment $payment): View
    {
        $this->authorizeOutlet($outlet);

        $payment->load('rentalTransaction.customer', 'rentalTransaction.rentalUnit.rentalItem');
        if ((int) $payment->rentalTransaction->outlet_id !== (int) $outlet->id) {
            abort(404);
        }

        return view('sewa.payments.show', compact('outlet', 'payment'));
    }
}