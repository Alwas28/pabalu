<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfitLossController extends Controller
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

        if (!$user->hasPermission('report.profit_loss')) {
            abort(403);
        }

        $outlet->load('outletType');
        $trackCogs = false; // laundry tidak memakai track_cogs

        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   today()->format('Y-m-d'));

        // ── PENDAPATAN ────────────────────────────────────────────────────
        $salesRow = DB::table('laundry_orders')
            ->where('outlet_id', $outlet->id)
            ->where('status', 'diambil')
            ->whereNotNull('paid_at')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->selectRaw('
                COALESCE(SUM(total), 0) as gross_sales,
                COUNT(*)                as total_trx
            ')
            ->first();

        $grossSales    = (int) $salesRow->gross_sales;
        $totalDiscount = 0;
        $totalTrx      = (int) $salesRow->total_trx;
        $netSales      = $grossSales;

        // ── HPP — laundry tidak memakai HPP ──────────────────────────────
        $totalHpp    = 0;
        $grossProfit = $netSales;

        // ── PENGELUARAN ───────────────────────────────────────────────────
        $expenseRows = DB::table('expenses')
            ->where('outlet_id', $outlet->id)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = (int) $expenseRows->sum('total');

        $allCategories = Expense::$categories
            + $outlet->expenseCategories()->pluck('label', 'slug')->toArray();

        $expenseByCategory = $expenseRows->map(function ($row) use ($allCategories) {
            $meta = Expense::getMeta($row->category);
            return [
                'slug'  => $row->category,
                'label' => $allCategories[$row->category] ?? $row->category,
                'total' => (int) $row->total,
                'icon'  => $meta['icon'],
                'color' => $meta['color'],
                'bg'    => $meta['bg'],
            ];
        });

        $netProfit = $grossProfit - $totalExpenses;

        // ── DATA HARIAN / BULANAN UNTUK CHART ────────────────────────────
        $daysDiff = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to));
        $groupBy  = $daysDiff > 62 ? 'month' : 'day';

        if ($groupBy === 'day') {
            $salesByPeriod = DB::table('laundry_orders')
                ->where('outlet_id', $outlet->id)
                ->where('status', 'diambil')
                ->whereNotNull('paid_at')
                ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
                ->selectRaw("DATE_FORMAT(paid_at,'%Y-%m-%d') as period, COALESCE(SUM(total),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');

            $expensesByPeriod = DB::table('expenses')
                ->where('outlet_id', $outlet->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m-%d') as period, COALESCE(SUM(amount),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');
        } else {
            $salesByPeriod = DB::table('laundry_orders')
                ->where('outlet_id', $outlet->id)
                ->where('status', 'diambil')
                ->whereNotNull('paid_at')
                ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
                ->selectRaw("DATE_FORMAT(paid_at,'%Y-%m') as period, COALESCE(SUM(total),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');

            $expensesByPeriod = DB::table('expenses')
                ->where('outlet_id', $outlet->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m') as period, COALESCE(SUM(amount),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');
        }

        $allPeriods = $salesByPeriod->keys()->merge($expensesByPeriod->keys())->unique()->sort()->values();

        $chartData = $allPeriods->map(function ($period) use ($salesByPeriod, $expensesByPeriod, $groupBy) {
            $label = $groupBy === 'day'
                ? \Carbon\Carbon::parse($period)->translatedFormat('d M')
                : \Carbon\Carbon::parse($period . '-01')->translatedFormat('M Y');
            return [
                'label'    => $label,
                'sales'    => (int) ($salesByPeriod[$period]?->total ?? 0),
                'expenses' => (int) ($expensesByPeriod[$period]?->total ?? 0),
            ];
        });

        return view('laundry.reports.profit-loss', compact(
            'outlet', 'user', 'trackCogs',
            'from', 'to', 'groupBy',
            'grossSales', 'totalDiscount', 'netSales', 'totalTrx',
            'totalHpp', 'grossProfit',
            'totalExpenses', 'expenseByCategory',
            'netProfit', 'chartData',
        ));
    }
}
