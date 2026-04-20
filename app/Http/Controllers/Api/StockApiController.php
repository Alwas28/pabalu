<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockApiController extends Controller
{
    private function checkOutletAccess(Request $request, int $outletId): ?JsonResponse
    {
        $ok = $request->user()->accessibleOutlets()->where('id', $outletId)->exists();
        return $ok ? null : response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
    }

    // ── Stok saat ini per produk ─────────────────────────
    public function index(Request $request): JsonResponse
    {
        $request->validate(['outlet_id' => ['required', 'exists:outlets,id']]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $tanggal  = today()->toDateString();
        $products = Product::where('outlet_id', $request->outlet_id)
            ->where('is_active', true)
            ->with('category:id,nama')
            ->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'nama'     => $p->nama,
                'category' => $p->category?->nama,
                'stok'     => StockMovement::currentStock($request->outlet_id, $p->id, $tanggal),
            ]);

        return response()->json($products);
    }

    // ── Opening stok ────────────────────────────────────
    public function opening(Request $request): JsonResponse
    {
        $request->validate(['outlet_id' => ['required', 'exists:outlets,id']]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $tanggal  = today()->toDateString();
        $products = Product::where('outlet_id', $request->outlet_id)
            ->where('is_active', true)
            ->with('category:id,nama')
            ->orderBy('nama')
            ->get();

        $existing = StockMovement::where('outlet_id', $request->outlet_id)
            ->where('type', 'opening')
            ->where('tanggal', $tanggal)
            ->pluck('qty', 'product_id');

        return response()->json([
            'tanggal'  => $tanggal,
            'products' => $products->map(fn($p) => [
                'id'            => $p->id,
                'nama'          => $p->nama,
                'category'      => $p->category?->nama,
                'qty_opening'   => $existing[$p->id] ?? 0,
                'stok_sekarang' => StockMovement::currentStock($request->outlet_id, $p->id, $tanggal),
            ]),
        ]);
    }

    public function storeOpening(Request $request): JsonResponse
    {
        if ($id = $request->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $id]);
        }

        $request->validate([
            'outlet_id'          => ['required', 'exists:outlets,id'],
            'tanggal'            => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:0'],
            'items.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        foreach ($request->items as $item) {
            if ((int) $item['qty'] === 0) continue;
            StockMovement::updateOrCreate(
                ['outlet_id' => $request->outlet_id, 'product_id' => $item['product_id'],
                 'type' => 'opening', 'tanggal' => $request->tanggal],
                ['qty' => $item['qty'], 'keterangan' => $item['keterangan'] ?? null,
                 'user_id' => $request->user()->id]
            );
        }

        ActivityLog::record('stock_opening',
            "Opening stok disimpan via API untuk outlet ID {$request->outlet_id}, tanggal {$request->tanggal}."
        );

        return response()->json(['message' => 'Opening stok berhasil disimpan.'], 201);
    }

    // ── Tambah Stok (in) ────────────────────────────────
    public function storeIn(Request $request): JsonResponse
    {
        return $this->storeMovement($request, 'in');
    }

    // ── Waste / Barang Rusak ─────────────────────────────
    public function storeWaste(Request $request): JsonResponse
    {
        return $this->storeMovement($request, 'waste');
    }

    private function storeMovement(Request $request, string $type): JsonResponse
    {
        if ($id = $request->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $id]);
        }

        $request->validate([
            'outlet_id'  => ['required', 'exists:outlets,id'],
            'product_id' => ['required', 'exists:products,id'],
            'tanggal'    => ['required', 'date'],
            'qty'        => ['required', 'integer', 'min:1'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $movement = StockMovement::create([
            'outlet_id'  => $request->outlet_id,
            'product_id' => $request->product_id,
            'type'       => $type,
            'tanggal'    => $request->tanggal,
            'qty'        => $request->qty,
            'keterangan' => $request->keterangan,
            'user_id'    => $request->user()->id,
        ]);

        $label = $type === 'in' ? 'Tambah stok' : 'Waste';
        ActivityLog::record("stock_{$type}",
            "{$label} {$request->qty} unit produk ID {$request->product_id} via API.",
            $movement
        );

        return response()->json(['message' => ucfirst($label) . ' berhasil disimpan.', 'id' => $movement->id], 201);
    }

    // ── Riwayat pergerakan stok ─────────────────────────
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'  => ['required', 'exists:outlets,id'],
            'type'       => ['nullable', 'in:opening,in,waste'],
            'product_id' => ['nullable', 'exists:products,id'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $movements = StockMovement::with(['product:id,nama', 'user:id,name'])
            ->where('outlet_id', $request->outlet_id)
            ->when($request->type,       fn($q) => $q->where('type', $request->type))
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->date_from,  fn($q) => $q->where('tanggal', '>=', $request->date_from))
            ->when($request->date_to,    fn($q) => $q->where('tanggal', '<=', $request->date_to))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'type'       => $m->type,
                'product'    => $m->product?->nama,
                'qty'        => $m->qty,
                'tanggal'    => $m->tanggal,
                'keterangan' => $m->keterangan,
                'user'       => $m->user?->name,
            ]);

        return response()->json($movements);
    }
}
