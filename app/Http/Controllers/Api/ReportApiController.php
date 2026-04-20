<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    private function checkOutletAccess(Request $request, int $outletId): ?JsonResponse
    {
        $ok = $request->user()->accessibleOutlets()->where('id', $outletId)->exists();
        return $ok ? null : response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
    }

    // ── Laporan Penjualan ────────────────────────────────
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $user      = $request->user();
        $dateFrom  = $request->date_from ?? today()->toDateString();
        $dateTo    = $request->date_to   ?? today()->toDateString();
        $kasirId   = $user->isKasir() ? $user->id : null;

        $transactions = Transaction::with('items')
            ->where('outlet_id', $request->outlet_id)
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->where('status', 'paid')
            ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
            ->orderBy('tanggal')
            ->get();

        $totalOmzet = $transactions->sum('total');
        $totalTrx   = $transactions->count();

        $perHari = $transactions->groupBy(fn($t) => $t->tanggal->toDateString())
            ->map(fn($group, $date) => [
                'tanggal' => $date,
                'jumlah'  => $group->count(),
                'omzet'   => (int) $group->sum('total'),
            ])->values()->sortBy('tanggal')->values();

        $itemIds   = $transactions->pluck('id');
        $perProduk = TransactionItem::whereIn('transaction_id', $itemIds)
            ->selectRaw('product_id, nama_produk, SUM(qty) as total_qty, SUM(subtotal) as total_subtotal')
            ->groupBy('product_id', 'nama_produk')
            ->orderByDesc('total_subtotal')
            ->get()
            ->map(fn($i) => [
                'nama'           => $i->nama_produk,
                'total_qty'      => (int) $i->total_qty,
                'total_subtotal' => (int) $i->total_subtotal,
            ]);

        return response()->json([
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
            'total_omzet'  => (int) $totalOmzet,
            'total_transaksi' => $totalTrx,
            'per_hari'     => $perHari,
            'per_produk'   => $perProduk,
        ]);
    }

    // ── Laporan Laba & Rugi ──────────────────────────────
    public function profitLoss(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $user     = $request->user();
        $dateFrom = $request->date_from ?? today()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? today()->toDateString();
        $kasirId  = $user->isKasir() ? $user->id : null;

        $start = Carbon::parse($dateFrom);
        $end   = Carbon::parse($dateTo);
        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        $txByDate = Transaction::where('outlet_id', $request->outlet_id)
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->where('status', 'paid')
            ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
            ->selectRaw('tanggal, SUM(total) as omzet')
            ->groupBy('tanggal')
            ->pluck('omzet', 'tanggal')
            ->toArray();

        $expByDate = $kasirId ? [] : Expense::where('outlet_id', $request->outlet_id)
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->selectRaw('tanggal, SUM(jumlah) as total_expense')
            ->groupBy('tanggal')
            ->pluck('total_expense', 'tanggal')
            ->toArray();

        $perHari = collect($dates)->map(fn($date) => [
            'tanggal' => $date,
            'omzet'   => (int) ($txByDate[$date]  ?? 0),
            'expense' => (int) ($expByDate[$date] ?? 0),
            'laba'    => (int) (($txByDate[$date] ?? 0) - ($expByDate[$date] ?? 0)),
        ]);

        $totalOmzet   = $perHari->sum('omzet');
        $totalExpense = $perHari->sum('expense');

        $expenseByKat = $kasirId ? [] : Expense::where('outlet_id', $request->outlet_id)
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => ['kategori' => $e->kategori, 'total' => (int) $e->total])
            ->toArray();

        return response()->json([
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
            'total_omzet'      => (int) $totalOmzet,
            'total_expense'    => (int) $totalExpense,
            'total_laba'       => (int) ($totalOmzet - $totalExpense),
            'per_hari'         => $perHari->values(),
            'expense_per_kategori' => $expenseByKat,
        ]);
    }
}
