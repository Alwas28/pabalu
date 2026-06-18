<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $today     = today()->format('Y-m-d');
        $yesterday = today()->subDay()->format('Y-m-d');

        $todayRevenue     = (int) $outlet->transactions()->where('status', 'completed')->whereDate('date', $today)->sum('total');
        $yesterdayRevenue = (int) $outlet->transactions()->where('status', 'completed')->whereDate('date', $yesterday)->sum('total');
        $todayTrxCount    = $outlet->transactions()->where('status', 'completed')->whereDate('date', $today)->count();
        $todayExpenses    = (int) $outlet->expenses()->whereDate('date', $today)->sum('amount');

        $revenueChangePct = $yesterdayRevenue > 0
            ? round(($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue * 100)
            : null;

        $criticalProducts = $outlet->products()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('stock')
            ->get();

        $recentTransactions = $outlet->transactions()
            ->with(['user', 'items'])
            ->where('status', 'completed')
            ->latest('id')
            ->limit(8)
            ->get();

        $topProducts = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->where('t.outlet_id', $outlet->id)
            ->where('t.status', 'completed')
            ->where('t.date', '>=', today()->subDays(6)->format('Y-m-d'))
            ->select('ti.product_name', DB::raw('SUM(ti.qty) as total_qty'), DB::raw('SUM(ti.subtotal) as total_revenue'))
            ->groupBy('ti.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $buildChart = function (int $days) use ($outlet) {
            $rows = DB::table('transactions')
                ->where('outlet_id', $outlet->id)
                ->where('status', 'completed')
                ->where('date', '>=', today()->subDays($days - 1)->format('Y-m-d'))
                ->selectRaw("DATE_FORMAT(date,'%Y-%m-%d') as d, COALESCE(SUM(total),0) as total")
                ->groupBy('d')
                ->get()->keyBy('d');

            return collect(range($days - 1, 0))
                ->map(fn($i) => today()->subDays($i)->format('Y-m-d'))
                ->mapWithKeys(fn($date) => [$date => (int) ($rows[$date]?->total ?? 0)]);
        };

        $chart7  = $buildChart(7);
        $chart30 = $buildChart(30);

        return view('fnb.show', compact(
            'outlet', 'user',
            'todayRevenue', 'yesterdayRevenue', 'revenueChangePct',
            'todayTrxCount', 'todayExpenses',
            'criticalProducts', 'recentTransactions', 'topProducts',
            'chart7', 'chart30',
        ));
    }
}
