<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockOpeningController extends Controller
{
    public function index(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = $request->get('tanggal', today()->toDateString());

        $outlet   = $outletId ? Outlet::find($outletId) : null;

        // Produk aktif di outlet ini
        $products = $outletId
            ? Product::where('outlet_id', $outletId)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('nama')
                ->get()
            : collect();

        // Opening yang sudah tersimpan untuk hari ini
        $existing = $outletId
            ? StockMovement::where('outlet_id', $outletId)
                ->where('type', 'opening')
                ->where('tanggal', $tanggal)
                ->pluck('qty', 'product_id')
            : collect();

        // Stok terkini per produk (opening + in - waste - sold) untuk tampilan
        $currentStock = [];
        foreach ($products as $p) {
            $currentStock[$p->id] = StockMovement::currentStock($outletId, $p->id, $tanggal);
        }

        return view('opening.index', compact(
            'outlets', 'outlet', 'outletId', 'assignedOutletId', 'tanggal',
            'products', 'existing', 'currentStock'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($assignedOutletId = auth()->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $assignedOutletId]);
        }

        $request->validate([
            'outlet_id'  => ['required', 'exists:outlets,id'],
            'tanggal'    => ['required', 'date'],
            'items'      => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:0'],
        ]);

        $outletId = $request->outlet_id;
        $tanggal  = $request->tanggal;
        $userId   = auth()->id();

        foreach ($request->items as $item) {
            if ((int)$item['qty'] === 0) continue;

            StockMovement::updateOrCreate(
                [
                    'outlet_id'  => $outletId,
                    'product_id' => $item['product_id'],
                    'type'       => 'opening',
                    'tanggal'    => $tanggal,
                ],
                [
                    'qty'        => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'user_id'    => $userId,
                ]
            );
        }

        ActivityLog::record('stock_opening',
            "Opening stok disimpan untuk outlet ID {$outletId}, tanggal {$tanggal}."
        );

        return redirect()->route('opening.index', [
            'outlet_id' => $outletId,
            'tanggal'   => $tanggal,
        ])->with('success', 'Opening stok berhasil disimpan.');
    }
}
