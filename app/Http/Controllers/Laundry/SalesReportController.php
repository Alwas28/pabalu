<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Exports\SalesReportExport;
use App\Models\LaundryOrder;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
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

    private function buildQuery(Outlet $outlet, Request $request)
    {
        $from          = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to            = $request->input('to',   today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');
        $userId        = $request->input('user_id');

        return LaundryOrder::where('outlet_id', $outlet->id)
            ->with(['user', 'items'])
            ->where('status', 'diambil')
            ->whereNotNull('paid_at')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($userId,        fn($q) => $q->where('user_id', $userId));
    }

    private function computeData(Request $request, Outlet $outlet): array
    {
        $from          = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to            = $request->input('to',   today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');
        $userId        = $request->input('user_id');

        $transactions = $this->buildQuery($outlet, $request)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $totalRevenue   = (int) $transactions->sum('total');
        $totalDiscount  = 0;
        $totalCount     = $transactions->count();
        $avgTransaction = $totalCount > 0 ? round($totalRevenue / $totalCount) : 0;

        // By payment method
        $byMethod = collect(['cash', 'qris_transfer', 'qris_pay', 'transfer', 'card'])
            ->mapWithKeys(fn($m) => [
                $m => [
                    'count' => $transactions->where('payment_method', $m)->count(),
                    'total' => (int) $transactions->where('payment_method', $m)->sum('total'),
                ],
            ]);

        // By day (for chart)
        $byDay = $transactions
            ->groupBy(fn($t) => $t->paid_at->format('Y-m-d'))
            ->map(fn($g) => ['count' => $g->count(), 'total' => (int) $g->sum('total')])
            ->sortKeys();

        // Top layanan
        $byProduct = DB::table('laundry_order_items as li')
            ->join('laundry_orders as lo', 'li.laundry_order_id', '=', 'lo.id')
            ->where('lo.outlet_id', $outlet->id)
            ->where('lo.status', 'diambil')
            ->whereNotNull('lo.paid_at')
            ->whereBetween(DB::raw('DATE(lo.paid_at)'), [$from, $to])
            ->when($paymentMethod, fn($q) => $q->where('lo.payment_method', $paymentMethod))
            ->when($userId,        fn($q) => $q->where('lo.user_id', $userId))
            ->select(
                'li.product_name',
                DB::raw('SUM(li.qty) as total_qty'),
                DB::raw('SUM(li.subtotal) as total_revenue'),
            )
            ->groupBy('li.product_name')
            ->orderByDesc('total_revenue')
            ->get();

        // Kasir yang pernah handle pesanan di outlet ini
        $cashiers = LaundryOrder::where('outlet_id', $outlet->id)
            ->where('status', 'diambil')
            ->whereNotNull('paid_at')
            ->with('user')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        return compact(
            'transactions', 'totalRevenue', 'totalDiscount', 'totalCount', 'avgTransaction',
            'byMethod', 'byDay', 'byProduct', 'cashiers',
            'from', 'to', 'paymentMethod', 'userId',
        );
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('report.sales')) {
            abort(403);
        }

        $outlet->load('outletType');
        $data = $this->computeData($request, $outlet);

        return view('laundry.reports.sales', array_merge($data, compact('outlet', 'user')));
    }

    public function export(Request $request, Outlet $outlet)
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('report.sales')) {
            abort(403);
        }

        $outlet->load('outletType');
        $data = $this->computeData($request, $outlet);

        $summary = [
            'total_transactions' => $data['totalCount'],
            'total_revenue'      => $data['totalRevenue'],
            'total_discount'     => $data['totalDiscount'],
            'avg_transaction'    => $data['avgTransaction'],
        ];

        $filename = 'laporan-pendapatan-'
            . $outlet->name . '-'
            . $data['from'] . '-sd-' . $data['to']
            . '.xlsx';

        return Excel::download(
            new SalesReportExport(
                summary:      $summary,
                byMethod:     $data['byMethod'],
                byProduct:    $data['byProduct'],
                transactions: $data['transactions'],
                from:         $data['from'],
                to:           $data['to'],
                outletName:   $outlet->name,
            ),
            $filename,
        );
    }
}
