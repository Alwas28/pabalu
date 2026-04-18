<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = $request->get('tanggal', today()->toDateString());

        $outlet   = $outletId ? $outlets->firstWhere('id', $outletId) : null;
        $products = collect();
        $summary  = collect();

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

                $sold = \App\Models\TransactionItem::whereHas('transaction', fn($q) =>
                        $q->where('outlet_id', $outletId)
                          ->where('tanggal', $tanggal)
                          ->where('status', 'paid')
                    )->where('product_id', $p->id)->sum('qty');

                $akhir = max(0, $opening + $in - $waste - $sold);

                return [
                    'id'       => $p->id,
                    'nama'     => $p->nama,
                    'kategori' => $p->category?->nama ?? 'Tanpa Kategori',
                    'satuan'   => $p->satuan,
                    'is_active'=> $p->is_active,
                    'opening'  => $opening,
                    'in'       => $in,
                    'waste'    => $waste,
                    'sold'     => $sold,
                    'akhir'    => $akhir,
                ];
            });
        }

        // Movements log (all types, current tanggal)
        $movements = collect();
        if ($outletId) {
            $movements = StockMovement::with(['product', 'user'])
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $threshold = (int) Setting::get('low_stock_threshold', 5);

        return view('stock.index', compact(
            'outlets', 'outletId', 'assignedOutletId', 'outlet', 'tanggal',
            'summary', 'movements', 'threshold'
        ));
    }
}
