<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RefundController extends Controller
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

    private function authorizeTransaction(Outlet $outlet, RentalTransaction $rental): void
    {
        if ((int) $rental->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $status = in_array($request->query('status'), ['pending', 'done'], true) ? $request->query('status') : 'pending';

        $finished = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('status', 'selesai')
            ->where('deposit_amount', '>', 0)
            ->with(['customer', 'rentalUnit.rentalItem', 'payments'])
            ->orderByDesc('returned_at')
            ->get();

        $pending = $finished->filter(fn ($r) => $r->needsRefund())->values();
        $done    = $finished->filter(fn ($r) => $r->refunded_at !== null)->values();

        $rentals = $status === 'done' ? $done : $pending;

        $totalPending = $pending->sum(fn ($r) => $r->depositBalance());
        $totalDone    = $done->sum(fn ($r) => (int) $r->refund_amount);

        return view('sewa.refunds.index', compact('outlet', 'user', 'rentals', 'status', 'totalPending', 'totalDone', 'pending', 'done'));
    }

    public function markRefunded(Outlet $outlet, RentalTransaction $rental): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeTransaction($outlet, $rental);

        if (!$rental->needsRefund()) {
            return back()->with('error', 'Sewa ini tidak memiliki refund yang menunggu.');
        }

        $rental->update([
            'refunded_at'   => now(),
            'refund_amount' => $rental->depositBalance(),
        ]);

        return back()->with('success', "Refund untuk sewa {$rental->order_number} berhasil ditandai selesai.");
    }
}
