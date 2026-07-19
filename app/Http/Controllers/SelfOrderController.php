<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SelfOrderController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) abort(403);
        return $user;
    }

    private function guardSelfOrder(Outlet $outlet, Order $order): void
    {
        if ($order->outlet_id != $outlet->id) abort(404);
        if (!is_null($order->user_id)) abort(404);
    }

    /** Inbox — daftar self-order yang menunggu review staff */
    public function index(Outlet $outlet): View
    {
        $this->authorizeOutlet($outlet);
        if (!$outlet->enable_self_order) abort(403, 'Fitur self-order tidak aktif untuk outlet ini.');

        $pending = Order::where('outlet_id', $outlet->id)
            ->whereNull('user_id')
            ->where('status', 'pending')
            ->with('items')
            ->orderBy('created_at')
            ->get();

        // Kumpulkan meja yang sudah punya order aktif (open/preparing/served)
        // supaya view bisa tampilkan peringatan "tambahan dari meja X"
        $activeTables = Order::where('outlet_id', $outlet->id)
            ->whereIn('status', ['open', 'preparing', 'served'])
            ->whereNotNull('table_number')
            ->pluck('table_number')
            ->unique()
            ->values()
            ->toArray();

        $recent = Order::where('outlet_id', $outlet->id)
            ->whereNull('user_id')
            ->whereIn('status', ['paid', 'cancelled'])
            ->with('items')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('self-orders.index', compact('outlet', 'pending', 'recent', 'activeTables'));
    }

    /** Halaman tinjau & edit sebelum masuk antrian */
    public function show(Outlet $outlet, Order $order): View|RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);
        if (!$outlet->enable_self_order) abort(403, 'Fitur self-order tidak aktif untuk outlet ini.');
        $this->guardSelfOrder($outlet, $order);

        if ($order->status !== 'pending') {
            return redirect()
                ->route($outlet->rp() . '.self-orders.index', $outlet)
                ->with('info', 'Pesanan ini sudah diproses.');
        }

        $order->load('items');
        $outlet->load('outletType');

        $products = $outlet->products()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('self-orders.show', compact('outlet', 'order', 'products', 'user'));
    }

    /** Masukkan self-order (dengan edit opsional) ke antrian normal */
    public function approve(Request $request, Outlet $outlet, Order $order): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);
        if (!$outlet->enable_self_order) abort(403, 'Fitur self-order tidak aktif untuk outlet ini.');
        $this->guardSelfOrder($outlet, $order);

        if ($order->status !== 'pending') {
            return redirect()
                ->route($outlet->rp() . '.self-orders.index', $outlet)
                ->with('info', 'Pesanan ini sudah diproses.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number'  => ['nullable', 'string', 'max:30'],
            'notes'         => ['nullable', 'string', 'max:500'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.qty'   => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $productIds = array_keys($data['items']);
        $products   = $outlet->products()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return back()->with('error', 'Tidak ada produk valid dalam pesanan.');
        }

        DB::transaction(function () use ($data, $order, $products, $user) {
            $order->items()->delete();

            $subtotal = 0;
            foreach ($data['items'] as $productId => $item) {
                $product = $products->get((int) $productId);
                if (!$product) continue;

                $lineTotal = $product->price * $item['qty'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_price' => $product->price,
                    'qty'           => $item['qty'],
                    'subtotal'      => $lineTotal,
                    'is_served'     => false,
                ]);
            }

            $order->update([
                'customer_name'    => $data['customer_name'],
                'table_number'     => $data['table_number'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'subtotal'         => $subtotal,
                'discount_percent' => 0,
                'discount_amount'  => 0,
                'total'            => $subtotal,
                'status'           => 'open',
                'user_id'          => $user->id,
            ]);
        });

        $redirectUrl = $outlet->rp() === 'fnb'
            ? route('fnb.orders.show', [$outlet, $order])
            : $outlet->route('self-orders.index');

        return redirect($redirectUrl)
            ->with('success', 'Pesanan ' . $order->order_number . ' berhasil dimasukkan ke antrian.');
    }

    /** Tolak (cancel) self-order dari inbox */
    public function reject(Outlet $outlet, Order $order): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        if (!$outlet->enable_self_order) abort(403, 'Fitur self-order tidak aktif untuk outlet ini.');
        $this->guardSelfOrder($outlet, $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan ' . $order->order_number . ' ditolak.');
    }

    /** Hapus self-order yang sudah dibatalkan — hanya owner */
    public function destroy(Outlet $outlet, Order $order): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);
        if (!$outlet->enable_self_order) abort(403, 'Fitur self-order tidak aktif untuk outlet ini.');
        $this->guardSelfOrder($outlet, $order);

        if ($user->role !== 'owner') abort(403);

        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Hanya pesanan yang dibatalkan yang bisa dihapus.');
        }

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return back()->with('success', 'Pesanan dihapus.');
    }
}
