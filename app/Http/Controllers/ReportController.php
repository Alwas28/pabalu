<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    // ── Laporan Penjualan ────────────────────────────────
    public function sales(Request $request): View
    {
        $user             = auth()->user();
        $outlets          = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = $user->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $dateFrom         = $request->get('date_from', today()->toDateString());
        $dateTo           = $request->get('date_to',   today()->toDateString());
        $kasirId          = $user->isKasir() ? $user->id : null;

        $transactions = collect();
        $perHari      = collect();
        $perProduk    = collect();
        $totalOmzet   = 0;
        $totalTrx     = 0;

        if ($outletId) {
            $transactions = Transaction::with(['kasir', 'items'])
                ->where('outlet_id', $outletId)
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->where('status', 'paid')
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
                ->orderBy('tanggal')
                ->get();

            $totalOmzet = $transactions->sum('total');
            $totalTrx   = $transactions->count();

            // Per hari
            $perHari = $transactions->groupBy(fn($t) => $t->tanggal->toDateString())
                ->map(fn($group, $date) => [
                    'tanggal' => $date,
                    'jumlah'  => $group->count(),
                    'omzet'   => $group->sum('total'),
                ])->values()->sortBy('tanggal')->values();

            // Per produk
            $itemIds = $transactions->pluck('id');
            $perProduk = TransactionItem::whereIn('transaction_id', $itemIds)
                ->selectRaw('product_id, nama_produk, SUM(qty) as total_qty, SUM(subtotal) as total_subtotal')
                ->groupBy('product_id', 'nama_produk')
                ->orderByDesc('total_subtotal')
                ->get();
        }

        return view('reports.sales', compact(
            'outlets', 'outletId', 'assignedOutletId', 'dateFrom', 'dateTo',
            'transactions', 'perHari', 'perProduk',
            'totalOmzet', 'totalTrx', 'kasirId'
        ));
    }

    // ── Laporan Stok ─────────────────────────────────────
    public function stock(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = $request->get('tanggal', today()->toDateString());

        $summary = collect();

        if ($outletId) {
            $products = Product::where('outlet_id', $outletId)
                ->with('category')
                ->orderBy('nama')
                ->get();

            $summary = $products->map(function ($p) use ($outletId, $tanggal) {
                $opening = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'opening')->where('tanggal', $tanggal)->sum('qty');
                $in = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'in')->where('tanggal', $tanggal)->sum('qty');
                $waste = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'waste')->where('tanggal', $tanggal)->sum('qty');
                $sold = TransactionItem::whereHas('transaction', fn($q) =>
                    $q->where('outlet_id', $outletId)->where('tanggal', $tanggal)->where('status', 'paid')
                )->where('product_id', $p->id)->sum('qty');

                return [
                    'nama'     => $p->nama,
                    'kategori' => $p->category?->nama ?? 'Tanpa Kategori',
                    'satuan'   => $p->satuan,
                    'opening'  => $opening,
                    'in'       => $in,
                    'waste'    => $waste,
                    'sold'     => $sold,
                    'akhir'    => max(0, $opening + $in - $waste - $sold),
                ];
            });
        }

        return view('reports.stock', compact('outlets', 'outletId', 'assignedOutletId', 'tanggal', 'summary'));
    }

    // ── Laporan Laba & Rugi ──────────────────────────────
    public function profitLoss(Request $request): View
    {
        $user             = auth()->user();
        $outlets          = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = $user->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $dateFrom         = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo           = $request->get('date_to',   today()->toDateString());
        $kasirId          = $user->isKasir() ? $user->id : null;

        $perHari     = collect();
        $totalOmzet  = 0;
        $totalExpense = 0;
        $expenseByKat = collect();

        if ($outletId) {
            $start = Carbon::parse($dateFrom);
            $end   = Carbon::parse($dateTo);
            $dates = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates[] = $d->toDateString();
            }

            $txByDate = Transaction::where('outlet_id', $outletId)
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->where('status', 'paid')
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
                ->selectRaw('tanggal, SUM(total) as omzet, COUNT(*) as jumlah_trx')
                ->groupBy('tanggal')
                ->pluck('omzet', 'tanggal')
                ->toArray();

            // Kasir tidak melihat expense (bukan tanggung jawab mereka)
            $expByDate = $kasirId ? [] : Expense::where('outlet_id', $outletId)
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->selectRaw('tanggal, SUM(jumlah) as total_expense')
                ->groupBy('tanggal')
                ->pluck('total_expense', 'tanggal')
                ->toArray();

            $perHari = collect($dates)->map(function ($date) use ($txByDate, $expByDate) {
                $omzet   = (float) ($txByDate[$date]  ?? 0);
                $expense = (float) ($expByDate[$date] ?? 0);
                return [
                    'tanggal' => $date,
                    'omzet'   => $omzet,
                    'expense' => $expense,
                    'laba'    => $omzet - $expense,
                ];
            });

            $totalOmzet   = $perHari->sum('omzet');
            $totalExpense = $perHari->sum('expense');

            $expenseByKat = $kasirId ? collect() : Expense::where('outlet_id', $outletId)
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->selectRaw('kategori, SUM(jumlah) as total')
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->get();
        }

        $totalLaba = $totalOmzet - $totalExpense;

        return view('reports.profit-loss', compact(
            'outlets', 'outletId', 'assignedOutletId', 'dateFrom', 'dateTo',
            'perHari', 'totalOmzet', 'totalExpense', 'totalLaba',
            'expenseByKat'
        ));
    }
}
