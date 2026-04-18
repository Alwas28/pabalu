<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    private const TYPES = [
        'in' => [
            'label'      => 'Tambah Stok',
            'title'      => 'Tambah Stok Masuk',
            'color'      => '#34d399',   // green
            'icon'       => 'arrow-down-to-line',
            'permission' => 'stock.in',
        ],
        'waste' => [
            'label'      => 'Catat Waste',
            'title'      => 'Catat Barang Rusak / Waste',
            'color'      => '#f87171',   // red
            'icon'       => 'triangle-exclamation',
            'permission' => 'stock.waste',
        ],
    ];

    // ── Form Input ───────────────────────────────────────
    public function create(Request $request, string $type): View
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        $config           = self::TYPES[$type];
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = today()->toDateString();

        $products   = collect();
        $wasteToday = collect();
        if ($outletId) {
            $products = Product::where('outlet_id', $outletId)
                ->where('is_active', true)
                ->orderBy('nama')
                ->get()
                ->map(function ($p) use ($outletId, $tanggal) {
                    $p->stok_sekarang = StockMovement::currentStock($outletId, $p->id, $tanggal);
                    return $p;
                });

            if ($type === 'waste') {
                $wasteToday = StockMovement::with(['product', 'user'])
                    ->where('outlet_id', $outletId)
                    ->where('type', 'waste')
                    ->where('tanggal', $tanggal)
                    ->latest()
                    ->get();
            }
        }

        return view('stock.movement', compact('type', 'config', 'outlets', 'outletId', 'assignedOutletId', 'products', 'tanggal', 'wasteToday'));
    }

    // ── Simpan ───────────────────────────────────────────
    public function store(Request $request, string $type): RedirectResponse
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        if ($assignedOutletId = auth()->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $assignedOutletId]);
        }

        $validated = $request->validate([
            'outlet_id'         => ['required', 'exists:outlets,id'],
            'tanggal'           => ['required', 'date'],
            'rows'              => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'exists:products,id'],
            'rows.*.qty'        => ['required', 'integer', 'min:1'],
            'rows.*.keterangan' => ['nullable', 'string', 'max:200'],
        ]);

        if ($type === 'waste') {
            $tanggal = $validated['tanggal'];
            foreach ($validated['rows'] as $i => $row) {
                $stok = StockMovement::currentStock($validated['outlet_id'], $row['product_id'], $tanggal);
                if ($row['qty'] > $stok) {
                    $product = Product::find($row['product_id']);
                    return back()->withInput()->withErrors([
                        "rows.{$i}.qty" => "Qty waste \"{$product->nama}\" ({$row['qty']}) melebihi stok tersedia ({$stok}).",
                    ]);
                }
            }
        }

        foreach ($validated['rows'] as $row) {
            StockMovement::create([
                'outlet_id'  => $validated['outlet_id'],
                'product_id' => $row['product_id'],
                'type'       => $type,
                'qty'        => $row['qty'],
                'tanggal'    => $validated['tanggal'],
                'keterangan' => $row['keterangan'] ?? null,
                'user_id'    => auth()->id(),
            ]);
        }

        $label = self::TYPES[$type]['label'];
        $count = count($validated['rows']);

        ActivityLog::record("stock_{$type}",
            "{$label} disimpan: {$count} produk (outlet ID {$validated['outlet_id']}, tanggal {$validated['tanggal']})."
        );

        return redirect()
            ->route("stock.{$type}", ['outlet_id' => $validated['outlet_id']])
            ->with('success', "{$label} berhasil disimpan ({$count} produk).");
    }

    // ── Riwayat ──────────────────────────────────────────
    public function history(Request $request, string $type): View
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        $config           = self::TYPES[$type];
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();

        $query = StockMovement::with(['product', 'outlet', 'user'])
            ->where('type', $type);

        $outletId = $assignedOutletId ?? $request->get('outlet_id');
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        if ($tanggal = $request->get('tanggal')) {
            $query->where('tanggal', $tanggal);
        } else {
            $query->where('tanggal', today()->toDateString());
        }

        $movements = $query->latest()->get();
        $totalQty  = $movements->sum('qty');

        return view('stock.history', compact('type', 'config', 'outlets', 'outletId', 'assignedOutletId', 'movements', 'totalQty'));
    }
}
