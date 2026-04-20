<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClosingApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'tanggal'   => ['nullable', 'date'],
        ]);

        $user     = $request->user();
        $outletId = $request->outlet_id;
        $ok       = $user->accessibleOutlets()->where('id', $outletId)->exists();
        if (! $ok) {
            return response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
        }

        $tanggal = $request->tanggal ?? today()->toDateString();

        // ── Transaksi paid ────────────────────────────────
        $transactions = Transaction::with('items')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->where('status', 'paid')
            ->get();

        $omzet    = $transactions->sum('total');
        $totalTrx = $transactions->count();

        // Rincian per metode bayar
        $perMetode = $transactions->groupBy('metode_bayar')
            ->map(fn($g) => ['jumlah' => $g->count(), 'total' => (int) $g->sum('total')])
            ->toArray();

        // ── Pengeluaran ───────────────────────────────────
        $expenses     = Expense::where('outlet_id', $outletId)->where('tanggal', $tanggal)->get();
        $totalExpense = $expenses->sum('jumlah');

        $expenseByKat = $expenses->groupBy('kategori')
            ->map(fn($g) => (int) $g->sum('jumlah'))
            ->toArray();

        // ── Stok per produk ───────────────────────────────
        $products = Product::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->with('category:id,nama')
            ->orderBy('nama')
            ->get();

        $stockSummary = $products->map(function ($p) use ($outletId, $tanggal) {
            $opening = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                ->where('type', 'opening')->where('tanggal', $tanggal)->sum('qty');
            $in      = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                ->where('type', 'in')->where('tanggal', $tanggal)->sum('qty');
            $waste   = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                ->where('type', 'waste')->where('tanggal', $tanggal)->sum('qty');
            $sold    = TransactionItem::whereHas('transaction', fn($q) =>
                    $q->where('outlet_id', $outletId)->where('tanggal', $tanggal)->where('status', 'paid')
                )->where('product_id', $p->id)->sum('qty');

            return [
                'product_id' => $p->id,
                'nama'       => $p->nama,
                'category'   => $p->category?->nama,
                'opening'    => (int) $opening,
                'in'         => (int) $in,
                'waste'      => (int) $waste,
                'sold'       => (int) $sold,
                'akhir'      => (int) max(0, $opening + $in - $waste - $sold),
            ];
        })->filter(fn($s) => $s['opening'] > 0 || $s['in'] > 0 || $s['waste'] > 0 || $s['sold'] > 0)->values();

        return response()->json([
            'tanggal'         => $tanggal,
            'omzet'           => (int) $omzet,
            'total_transaksi' => $totalTrx,
            'total_expense'   => (int) $totalExpense,
            'laba_kotor'      => (int) ($omzet - $totalExpense),
            'per_metode'      => $perMetode,
            'expense_per_kategori' => $expenseByKat,
            'stock_summary'   => $stockSummary,
        ]);
    }
}
