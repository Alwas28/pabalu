<?php

namespace App\Http\Controllers\Sewa;

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
        $trackCogs = false; // rental tidak punya HPP per unit

        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', today()->format('Y-m-d'));

        // ── PENDAPATAN ────────────────────────────────────────────────────
        // Omset dihitung dari pembayaran yang benar-benar tercatat (rental_payments),
        // bukan dari total_amount transaksi — karena sewa bisa dibayar bertahap (DP),
        // lewat potongan deposit, atau menyertakan denda.
        $salesRow = DB::table('rental_payments')
            ->join('rental_transactions', 'rental_transactions.id', '=', 'rental_payments.rental_transaction_id')
            ->where('rental_transactions.outlet_id', $outlet->id)
            ->whereBetween(DB::raw('DATE(rental_payments.paid_at)'), [$from, $to])
            ->selectRaw('
                COALESCE(SUM(rental_payments.amount), 0)               as gross_sales,
                COUNT(DISTINCT rental_payments.rental_transaction_id)  as total_trx
            ')
            ->first();

        $grossSales    = (int) $salesRow->gross_sales;
        $totalDiscount = 0;
        $totalTrx      = (int) $salesRow->total_trx;
        $netSales      = $grossSales;

        // ── HPP — rental tidak memakai HPP ────────────────────────────────
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
        $dateFmt  = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';

        $salesByPeriod = DB::table('rental_payments')
            ->join('rental_transactions', 'rental_transactions.id', '=', 'rental_payments.rental_transaction_id')
            ->where('rental_transactions.outlet_id', $outlet->id)
            ->whereBetween(DB::raw('DATE(rental_payments.paid_at)'), [$from, $to])
            ->selectRaw("DATE_FORMAT(rental_payments.paid_at,'{$dateFmt}') as period, COALESCE(SUM(rental_payments.amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

        $expensesByPeriod = DB::table('expenses')
            ->where('outlet_id', $outlet->id)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("DATE_FORMAT(date,'{$dateFmt}') as period, COALESCE(SUM(amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

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

        return view('sewa.reports.profit-loss', compact(
            'outlet', 'user', 'trackCogs',
            'from', 'to', 'groupBy',
            'grossSales', 'totalDiscount', 'netSales', 'totalTrx',
            'totalHpp', 'grossProfit',
            'totalExpenses', 'expenseByCategory',
            'netProfit', 'chartData',
        ));
    }
}
