<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\RentalTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FineReportController extends Controller
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

        if (!$user->hasPermission('report.sales')) {
            abort(403);
        }

        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', today()->format('Y-m-d'));

        // Denda yang ditambahkan (dilihat dari updated_at, karena tidak ada kolom "kapan denda dicatat")
        $rentals = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('fine_amount', '>', 0)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$from, $to])
            ->with(['customer', 'rentalUnit.rentalItem', 'payments'])
            ->get();

        $totalFines     = (int) $rentals->sum('fine_amount');
        $totalPaid      = (int) $rentals->sum(fn ($r) => $r->finePaid());
        $totalRemaining = (int) $rentals->sum(fn ($r) => $r->fineRemaining());
        $totalCount     = $rentals->count();

        // Pembayaran denda aktual dalam periode (untuk tren "denda dibayar")
        $finePayments = DB::table('rental_payments as rp')
            ->join('rental_transactions as rt', 'rt.id', '=', 'rp.rental_transaction_id')
            ->where('rt.outlet_id', $outlet->id)
            ->where('rp.is_fine', true)
            ->whereBetween(DB::raw('DATE(rp.paid_at)'), [$from, $to])
            ->get();

        $totalFinePaidInPeriod = (int) $finePayments->sum('amount');

        // Tren denda ditambahkan per hari/bulan
        $daysDiff = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to));
        $groupBy  = $daysDiff > 62 ? 'month' : 'day';
        $dateFmt  = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';

        $addedRows = DB::table('rental_transactions')
            ->where('outlet_id', $outlet->id)
            ->where('fine_amount', '>', 0)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$from, $to])
            ->selectRaw("DATE_FORMAT(updated_at,'{$dateFmt}') as period, COALESCE(SUM(fine_amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

        $paidRows = DB::table('rental_payments as rp')
            ->join('rental_transactions as rt', 'rt.id', '=', 'rp.rental_transaction_id')
            ->where('rt.outlet_id', $outlet->id)
            ->where('rp.is_fine', true)
            ->whereBetween(DB::raw('DATE(rp.paid_at)'), [$from, $to])
            ->selectRaw("DATE_FORMAT(rp.paid_at,'{$dateFmt}') as period, COALESCE(SUM(rp.amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

        $allPeriods = $addedRows->keys()->merge($paidRows->keys())->unique()->sort()->values();

        $chartData = $allPeriods->map(function ($period) use ($addedRows, $paidRows, $groupBy) {
            $label = $groupBy === 'day'
                ? \Carbon\Carbon::parse($period)->translatedFormat('d M')
                : \Carbon\Carbon::parse($period . '-01')->translatedFormat('M Y');
            return [
                'label' => $label,
                'added' => (int) ($addedRows[$period]?->total ?? 0),
                'paid'  => (int) ($paidRows[$period]?->total ?? 0),
            ];
        });

        // Top pelanggan dengan denda terbanyak
        $topCustomers = $rentals->groupBy(fn ($r) => $r->customer->name)
            ->map(fn ($g) => (int) $g->sum('fine_amount'))
            ->sortDesc()
            ->take(5);

        return view('sewa.reports.fines', compact(
            'outlet', 'user', 'from', 'to', 'groupBy',
            'totalFines', 'totalPaid', 'totalRemaining', 'totalCount', 'totalFinePaidInPeriod',
            'chartData', 'topCustomers', 'rentals'
        ));
    }
}
