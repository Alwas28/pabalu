<?php

namespace App\Http\Controllers\Salon;

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
        $trackCogs = $outlet->outletType?->track_cogs ?? false;

        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   today()->format('Y-m-d'));

        // â”€â”€ PENDAPATAN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $salesRow = DB::table('transactions')
            ->where('outlet_id', $outlet->id)
            ->where('status', 'completed')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                COALESCE(SUM(total), 0)           as gross_sales,
                COALESCE(SUM(discount_amount), 0) as total_discount,
                COUNT(*)                           as total_trx
            ')
            ->first();

        $grossSales    = (int) $salesRow->gross_sales;
        $totalDiscount = (int) $salesRow->total_discount;
        $totalTrx      = (int) $salesRow->total_trx;
        $netSales      = $grossSales; // total sudah net (setelah diskon)

        // â”€â”€ HPP (hanya jika track_cogs) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $totalHpp = 0;
        if ($trackCogs) {
            $totalHpp = (int) DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->leftJoin('products as p', 'ti.product_id', '=', 'p.id')
                ->where('t.outlet_id', $outlet->id)
                ->where('t.status', 'completed')
                ->whereBetween('t.date', [$from, $to])
                ->selectRaw('COALESCE(SUM(ti.qty * COALESCE(p.cost_price, 0)), 0) as hpp')
                ->value('hpp');
        }

        $grossProfit = $netSales - $totalHpp;

        // â”€â”€ PENGELUARAN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $expenseRows = DB::table('expenses')
            ->where('outlet_id', $outlet->id)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = (int) $expenseRows->sum('total');

        // Merge with labels
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

        // â”€â”€ DATA HARIAN / BULANAN UNTUK CHART â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $daysDiff = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to));
        $groupBy  = $daysDiff > 62 ? 'month' : 'day';

        if ($groupBy === 'day') {
            $salesByPeriod = DB::table('transactions')
                ->where('outlet_id', $outlet->id)
                ->where('status', 'completed')
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m-%d') as period, COALESCE(SUM(total),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');

            $expensesByPeriod = DB::table('expenses')
                ->where('outlet_id', $outlet->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m-%d') as period, COALESCE(SUM(amount),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');
        } else {
            $salesByPeriod = DB::table('transactions')
                ->where('outlet_id', $outlet->id)
                ->where('status', 'completed')
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m') as period, COALESCE(SUM(total),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');

            $expensesByPeriod = DB::table('expenses')
                ->where('outlet_id', $outlet->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("DATE_FORMAT(date,'%Y-%m') as period, COALESCE(SUM(amount),0) as total")
                ->groupBy('period')
                ->get()->keyBy('period');
        }

        // Build unified period list
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

        return view('salon.reports.profit-loss', compact(
            'outlet', 'user', 'trackCogs',
            'from', 'to', 'groupBy',
            'grossSales', 'totalDiscount', 'netSales', 'totalTrx',
            'totalHpp', 'grossProfit',
            'totalExpenses', 'expenseByCategory',
            'netProfit', 'chartData',
        ));
    }
}
