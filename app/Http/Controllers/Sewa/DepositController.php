<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DepositController extends Controller
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

    /** Status ringkas deposit sebuah transaksi, dipakai untuk badge & filter pada halaman ini. */
    public static function depositStatus(RentalTransaction $rental): string
    {
        if ($rental->status === 'aktif') {
            return 'aktif';
        }
        if ($rental->refunded_at !== null) {
            return 'direfund';
        }
        return $rental->depositBalance() > 0 ? 'menunggu' : 'habis';
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

        $query = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('deposit_amount', '>', 0)
            ->with(['customer', 'rentalUnit.rentalItem', 'payments']);

        if ($from) $query->whereDate('start_at', '>=', $from);
        if ($to)   $query->whereDate('start_at', '<=', $to);

        $rentals = (clone $query)->orderByDesc('start_at')->paginate(25)->withQueryString();

        $all = (clone $query)->get();
        $totalHeld     = $all->filter(fn ($r) => self::depositStatus($r) === 'aktif')->sum(fn ($r) => $r->depositAvailable());
        $totalPending  = $all->filter(fn ($r) => self::depositStatus($r) === 'menunggu')->sum(fn ($r) => $r->depositBalance());
        $totalRefunded = $all->filter(fn ($r) => self::depositStatus($r) === 'direfund')->sum(fn ($r) => (int) $r->refund_amount);

        return view('sewa.deposits.index', compact('outlet', 'user', 'rentals', 'totalHeld', 'totalPending', 'totalRefunded', 'from', 'to'));
    }
}
