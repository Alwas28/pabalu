<?php

namespace App\Http\Controllers\Retail;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\User;
use App\Models\Waste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WasteController extends Controller
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

        if (!$user->hasPermission('stock.waste')) {
            abort(403);
        }

        $wastes = $outlet->wastes()
            ->with(['user', 'items'])
            ->when($request->filled('from'), fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->filled('to'),   fn($q) => $q->whereDate('date', '<=', $request->to))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('retail.waste.index', compact('outlet', 'user', 'wastes'));
    }

    public function create(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.waste')) {
            abort(403);
        }

        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $reasons = Waste::$reasons;

        return view('retail.waste.create', compact('outlet', 'user', 'products', 'reasons'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.waste')) {
            abort(403);
        }

        $reasonKeys = implode(',', array_keys(Waste::$reasons));

        $request->validate([
            'date'               => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:500'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.reason'     => ['required', 'string', "in:{$reasonKeys}"],
            'items.*.notes'      => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $outlet, $user) {
            $waste = Waste::create([
                'outlet_id'    => $outlet->id,
                'user_id'      => $user->id,
                'date'         => $request->date,
                'notes'        => $request->notes,
                'submitted_at' => now(),
            ]);

            foreach ($request->items as $item) {
                $product     = null;
                $productName = '';

                if (!empty($item['product_id'])) {
                    $product = $outlet->products()->find((int) $item['product_id']);
                }

                if ($product) {
                    $productName = $product->name;
                } else {
                    continue; // skip rows without a valid product
                }

                $qty = (int) $item['qty'];

                $waste->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $productName,
                    'qty'          => $qty,
                    'reason'       => $item['reason'],
                    'notes'        => $item['notes'] ?? null,
                ]);

                // Decrement stock â€” never below 0
                $newStock = max(0, $product->stock - $qty);
                $product->update(['stock' => $newStock]);
            }
        });

        return redirect($outlet->route('waste.index'))
            ->with('success', 'Laporan waste berhasil disimpan. Stok produk dikurangi.');
    }

    public function show(Outlet $outlet, Waste $waste): View
    {
        $user = $this->authorizeOutlet($outlet);

        if ($waste->outlet_id !== $outlet->id) {
            abort(404);
        }

        $waste->load(['items.product.category', 'user']);
        $reasons = Waste::$reasons;

        return view('retail.waste.show', compact('outlet', 'user', 'waste', 'reasons'));
    }

    public function destroy(Outlet $outlet, Waste $waste): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.waste')) {
            abort(403);
        }

        if ($waste->outlet_id !== $outlet->id) {
            abort(404);
        }

        DB::transaction(function () use ($outlet, $waste) {
            $waste->load('items');

            foreach ($waste->items as $item) {
                if ($item->product_id) {
                    $outlet->products()
                        ->where('id', $item->product_id)
                        ->increment('stock', $item->qty);
                }
            }

            $waste->delete();
        });

        return back()->with('success', 'Laporan waste dihapus. Stok produk dikembalikan.');
    }
}
