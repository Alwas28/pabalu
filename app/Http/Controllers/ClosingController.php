<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClosingController extends Controller
{
    public function index(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = $request->get('tanggal', today()->toDateString());

        $outlet   = $outletId ? Outlet::find($outletId) : null;

        // ── Summary data ────────────────────────────────
        $omzet        = 0;
        $totalTrx     = 0;
        $totalExpense = 0;
        $stockSummary = collect();
        $expenses     = collect();
        $transactions = collect();

        if ($outletId) {
            // Transaksi paid
            $transactions = Transaction::with('items')
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->where('status', 'paid')
                ->get();

            $omzet    = $transactions->sum('total');
            $totalTrx = $transactions->count();

            // Pengeluaran
            $expenses     = Expense::with('user')
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->get();

            $totalExpense = $expenses->sum('jumlah');

            // Stok per produk
            $products = Product::where('outlet_id', $outletId)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('nama')
                ->get();

            $stockSummary = $products->map(function ($p) use ($outletId, $tanggal) {
                $opening = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'opening')->where('tanggal', $tanggal)->sum('qty');

                $in = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'in')->where('tanggal', $tanggal)->sum('qty');

                $waste = StockMovement::where('outlet_id', $outletId)->where('product_id', $p->id)
                    ->where('type', 'waste')->where('tanggal', $tanggal)->sum('qty');

                $sold = \App\Models\TransactionItem::whereHas('transaction', fn($q) =>
                        $q->where('outlet_id', $outletId)
                          ->where('tanggal', $tanggal)
                          ->where('status', 'paid')
                    )->where('product_id', $p->id)->sum('qty');

                $akhir = max(0, $opening + $in - $waste - $sold);

                return (object) [
                    'product'  => $p,
                    'opening'  => $opening,
                    'in'       => $in,
                    'waste'    => $waste,
                    'sold'     => $sold,
                    'akhir'    => $akhir,
                ];
            })->filter(fn($s) => $s->opening > 0 || $s->in > 0 || $s->waste > 0 || $s->sold > 0);
        }

        $labaKotor = $omzet - $totalExpense;

        return view('closing.index', compact(
            'outlets', 'outletId', 'assignedOutletId', 'outlet', 'tanggal',
            'omzet', 'totalTrx', 'totalExpense', 'labaKotor',
            'stockSummary', 'expenses', 'transactions'
        ));
    }
}
