<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ResolvesBusinessDate;
use App\Models\Outlet;
use App\Models\StockOpname;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpeningStockController extends Controller
{
    use ResolvesBusinessDate;

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

        if (!$user->hasPermission('stock.opening')) {
            abort(403);
        }

        $outlet->load('outletType');

        if (!($outlet->outletType?->requires_opening_stock ?? false)) {
            abort(404);
        }

        $today = today();

        // Opname opening hari ini (jika sudah ada)
        $opname = $outlet->stockOpnames()
            ->with(['items.product.category', 'user'])
            ->where('date', $today)
            ->where('type', 'opening')
            ->first();

        // Produk aktif dikelompokkan per kategori (untuk form)
        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => $p->category?->name ?? 'Tanpa Kategori');

        // Histori dengan filter + paginate
        $history = $outlet->stockOpnames()
            ->with(['user', 'items'])
            ->where('type', 'opening')
            ->when($request->filled('hist_from'), fn ($q) => $q->whereDate('date', '>=', $request->hist_from))
            ->when($request->filled('hist_to'),   fn ($q) => $q->whereDate('date', '<=', $request->hist_to))
            ->orderByDesc('date')
            ->paginate(15, ['*'], 'hist_page')
            ->withQueryString();

        $closingDone = $outlet->dailyClosings()->where('date', $today)->exists();

        return view('fnb.opening.index', compact('outlet', 'user', 'opname', 'products', 'today', 'history', 'closingDone'));
    }

    public function show(Outlet $outlet, StockOpname $stockOpname): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        if (!($outlet->outletType?->requires_opening_stock ?? false)) {
            abort(404);
        }

        if ($stockOpname->outlet_id !== $outlet->id || $stockOpname->type !== 'opening') {
            abort(404);
        }

        $stockOpname->load(['items.product.category', 'user']);

        $closingDone = $outlet->dailyClosings()->where('date', $stockOpname->date)->exists();

        $canCancel = $user->hasPermission('stock.opening')
            && $stockOpname->date->isToday()
            && !$closingDone;

        return view('fnb.opening.show', compact('outlet', 'user', 'stockOpname', 'canCancel'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.opening')) {
            abort(403);
        }

        if (!($outlet->outletType?->requires_opening_stock ?? false)) {
            abort(404);
        }

        $today = today()->toDateString();

        // Blokir jika sesi bisnis sebelumnya masih terbuka (belum di-closing)
        $outlet->load(['outletType', 'dailyClosings']);
        if ($this->isBusinessDayOpen($outlet)) {
            $businessDate = $this->getBusinessDate($outlet);
            if ($businessDate !== $today) {
                return back()->with('error',
                    'Sesi bisnis tanggal ' . \Carbon\Carbon::parse($businessDate)->translatedFormat('d F Y') .
                    ' belum di-closing. Lakukan closing terlebih dahulu sebelum opening stok baru.'
                );
            }
        }

        if ($outlet->stockOpnames()->where('date', $today)->where('type', 'opening')->exists()) {
            return back()->with('error', 'Opening stok hari ini sudah dilakukan.');
        }

        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.actual_qty' => ['required', 'integer', 'min:0'],
            'items.*.notes'      => ['nullable', 'string', 'max:200'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $outlet, $user, $today) {
            $opname = StockOpname::create([
                'outlet_id'    => $outlet->id,
                'user_id'      => $user->id,
                'date'         => $today,
                'type'         => 'opening',
                'status'       => 'submitted',
                'notes'        => $request->notes,
                'submitted_at' => now(),
            ]);

            foreach ($request->items as $productId => $item) {
                $product = $outlet->products()->find((int) $productId);
                if (!$product) continue;

                $opname->items()->create([
                    'product_id' => $product->id,
                    'system_qty' => $product->stock,
                    'actual_qty' => (int) $item['actual_qty'],
                    'notes'      => $item['notes'] ?? null,
                ]);

                // Update stok produk ke hasil hitung fisik
                $product->update(['stock' => (int) $item['actual_qty']]);
            }
        });

        return back()->with('success', 'Opening stok berhasil disimpan. Stok produk telah diperbarui.');
    }

    public function destroy(Outlet $outlet, StockOpname $stockOpname): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.opening')) {
            abort(403);
        }

        if ($stockOpname->outlet_id !== $outlet->id) {
            abort(404);
        }

        if (!$stockOpname->date->isToday()) {
            return back()->with('error', 'Hanya opening hari ini yang dapat dibatalkan.');
        }

        // Blokir jika closing sudah dilakukan untuk tanggal ini
        $closingDone = $outlet->dailyClosings()->where('date', $stockOpname->date)->exists();

        if ($closingDone) {
            return back()->with('error', 'Tidak dapat membatalkan opening stok karena closing harian sudah dilakukan.');
        }

        // Blokir pembatalan jika sudah ada transaksi penjualan hari ini
        $adaTransaksi = Transaction::where('outlet_id', $outlet->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->exists();

        if ($adaTransaksi) {
            return back()->with('error', 'Tidak dapat membatalkan opening stok karena sudah ada transaksi penjualan hari ini.');
        }

        $stockOpname->load('items');

        DB::transaction(function () use ($outlet, $stockOpname) {
            foreach ($stockOpname->items as $item) {
                $product = $outlet->products()->find($item->product_id);
                if (!$product) continue;

                // Kembalikan ke stok sebelum opening (system_qty saat opening dicatat)
                $product->update(['stock' => $item->system_qty]);
            }
            $stockOpname->delete();
        });

        return back()->with('success', 'Opening dibatalkan. Stok produk dikembalikan ke kondisi sebelum opening.');
    }
}
