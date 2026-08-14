<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\RentalPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RevenueReportController extends Controller
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

    private function computeData(Request $request, Outlet $outlet): array
    {
        $from          = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to            = $request->input('to', today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');

        $payments = RentalPayment::whereHas('rentalTransaction', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->with(['rentalTransaction.customer', 'rentalTransaction.rentalUnit.rentalItem'])
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->when($paymentMethod, fn ($q) => $q->where('method', $paymentMethod))
            ->orderByDesc('paid_at')
            ->get();

        $totalRevenue = (int) $payments->sum('amount');
        $totalCount   = $payments->count();
        $avgPayment   = $totalCount > 0 ? round($totalRevenue / $totalCount) : 0;

        // Per metode pembayaran
        $byMethod = collect(array_keys(RentalPayment::METHODS))
            ->mapWithKeys(fn ($m) => [
                $m => [
                    'count' => $payments->where('method', $m)->count(),
                    'total' => (int) $payments->where('method', $m)->sum('amount'),
                ],
            ]);

        // Per hari (untuk chart)
        $byDay = $payments
            ->groupBy(fn ($p) => $p->paid_at->format('Y-m-d'))
            ->map(fn ($g) => ['count' => $g->count(), 'total' => (int) $g->sum('amount')])
            ->sortKeys();

        // Top barang disewa
        $byItem = DB::table('rental_payments as rp')
            ->join('rental_transactions as rt', 'rt.id', '=', 'rp.rental_transaction_id')
            ->join('rental_units as ru', 'ru.id', '=', 'rt.rental_unit_id')
            ->join('rental_items as ri', 'ri.id', '=', 'ru.rental_item_id')
            ->where('rt.outlet_id', $outlet->id)
            ->whereBetween(DB::raw('DATE(rp.paid_at)'), [$from, $to])
            ->when($paymentMethod, fn ($q) => $q->where('rp.method', $paymentMethod))
            ->select(
                'ri.name as item_name',
                DB::raw('COUNT(DISTINCT rt.id) as total_trx'),
                DB::raw('SUM(rp.amount) as total_revenue'),
            )
            ->groupBy('ri.name')
            ->orderByDesc('total_revenue')
            ->get();

        return compact('payments', 'totalRevenue', 'totalCount', 'avgPayment', 'byMethod', 'byDay', 'byItem', 'from', 'to', 'paymentMethod');
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('report.sales')) {
            abort(403);
        }

        $outlet->load('outletType');
        $data = $this->computeData($request, $outlet);

        return view('sewa.reports.revenue', array_merge($data, compact('outlet', 'user')));
    }
}
