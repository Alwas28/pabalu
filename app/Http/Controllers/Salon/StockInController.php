<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockInController extends Controller
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

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        $outlet->load('outletType');

        $stockIns = $outlet->stockIns()
            ->with(['user', 'items'])
            ->when($request->filled('search'),    fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('supplier', 'like', '%'.$request->search.'%')
                   ->orWhere('reference_no', 'like', '%'.$request->search.'%');
            }))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $thisMonth = $outlet->stockIns()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->with('items')
            ->get();

        $statTrx      = $thisMonth->count();
        $statQty      = $thisMonth->sum(fn ($s) => $s->items->sum('qty'));
        $statProducts = $thisMonth->flatMap(fn ($s) => $s->items->pluck('product_id'))->unique()->count();
        $hasFilter    = $request->hasAny(['search', 'date_from', 'date_to']);

        return view('salon.stock-in.index', compact(
            'outlet', 'user', 'stockIns', 'statTrx', 'statQty', 'statProducts', 'hasFilter'
        ));
    }

    public function create(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        $outlet->load('outletType');

        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => $p->category?->name ?? 'Tanpa Kategori');

        return view('salon.stock-in.create', compact('outlet', 'user', 'products'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        $request->validate([
            'date'              => ['required', 'date'],
            'supplier'          => ['nullable', 'string', 'max:150'],
            'reference_no'      => ['nullable', 'string', 'max:60'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'items'             => ['required', 'array'],
            'items.*.qty'       => ['nullable', 'integer', 'min:0'],
            'items.*.notes'     => ['nullable', 'string', 'max:200'],
        ]);

        // Hanya item dengan qty > 0
        $incoming = collect($request->items)
            ->filter(fn ($item) => filled($item['qty'] ?? null) && (int) $item['qty'] > 0);

        if ($incoming->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Minimal satu produk harus diisi jumlahnya.');
        }

        DB::transaction(function () use ($request, $outlet, $user, $incoming) {
            $stockIn = StockIn::create([
                'outlet_id'    => $outlet->id,
                'user_id'      => $user->id,
                'date'         => $request->date,
                'supplier'     => $request->supplier,
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            foreach ($incoming as $productId => $item) {
                $product = $outlet->products()->find((int) $productId);
                if (!$product) continue;

                $qty = (int) $item['qty'];

                $stockIn->items()->create([
                    'product_id' => $product->id,
                    'qty'        => $qty,
                    'notes'      => $item['notes'] ?? null,
                ]);

                $product->increment('stock', $qty);
            }
        });

        return redirect($outlet->route('stock-in.index'))
            ->with('success', 'Tambah stok berhasil disimpan. Stok produk telah diperbarui.');
    }

    public function show(Outlet $outlet, StockIn $stockIn): View
    {
        $user = $this->authorizeOutlet($outlet);

        if ($stockIn->outlet_id != $outlet->id) {
            abort(404);
        }

        $stockIn->load(['user', 'items.product.category']);

        return view('salon.stock-in.show', compact('outlet', 'user', 'stockIn'));
    }

    public function edit(Outlet $outlet, StockIn $stockIn): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        if ($stockIn->outlet_id != $outlet->id) {
            abort(404);
        }

        $outlet->load('outletType');
        $stockIn->load('items');

        // Existing qtys and notes keyed by product_id
        $existingItems = $stockIn->items->keyBy('product_id');

        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => $p->category?->name ?? 'Tanpa Kategori');

        return view('salon.stock-in.edit', compact('outlet', 'user', 'stockIn', 'products', 'existingItems'));
    }

    public function update(Request $request, Outlet $outlet, StockIn $stockIn): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        if ($stockIn->outlet_id != $outlet->id) {
            abort(404);
        }

        $request->validate([
            'date'              => ['required', 'date'],
            'supplier'          => ['nullable', 'string', 'max:150'],
            'reference_no'      => ['nullable', 'string', 'max:60'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'items'             => ['required', 'array'],
            'items.*.qty'       => ['nullable', 'integer', 'min:0'],
            'items.*.notes'     => ['nullable', 'string', 'max:200'],
        ]);

        $incoming = collect($request->items)
            ->filter(fn ($item) => filled($item['qty'] ?? null) && (int) $item['qty'] > 0);

        if ($incoming->isEmpty()) {
            return back()->withInput()->with('error', 'Minimal satu produk harus diisi jumlahnya.');
        }

        DB::transaction(function () use ($request, $outlet, $stockIn, $incoming) {
            // Reverse existing stock additions
            foreach ($stockIn->items as $item) {
                $product = $outlet->products()->find($item->product_id);
                if ($product) {
                    $product->decrement('stock', $item->qty);
                }
            }

            // Remove all existing items
            $stockIn->items()->delete();

            // Update header info
            $stockIn->update([
                'date'         => $request->date,
                'supplier'     => $request->supplier,
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            // Re-apply new items
            foreach ($incoming as $productId => $item) {
                $product = $outlet->products()->find((int) $productId);
                if (!$product) continue;

                $qty = (int) $item['qty'];
                $stockIn->items()->create([
                    'product_id' => $product->id,
                    'qty'        => $qty,
                    'notes'      => $item['notes'] ?? null,
                ]);
                $product->increment('stock', $qty);
            }
        });

        return redirect($outlet->route('stock-in.show', [$stockIn]))
            ->with('success', 'Data tambah stok diperbarui. Stok produk telah disesuaikan.');
    }

    public function destroy(Outlet $outlet, StockIn $stockIn): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.in')) {
            abort(403);
        }

        if ($stockIn->outlet_id != $outlet->id) {
            abort(404);
        }

        $stockIn->load('items');

        DB::transaction(function () use ($outlet, $stockIn) {
            // Reverse all stock additions before deleting
            foreach ($stockIn->items as $item) {
                $product = $outlet->products()->find($item->product_id);
                if ($product) {
                    $product->decrement('stock', $item->qty);
                }
            }
            $stockIn->delete();
        });

        return redirect($outlet->route('stock-in.index'))
            ->with('success', 'Data tambah stok dihapus. Stok produk telah dikembalikan.');
    }
}
