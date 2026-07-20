<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuController extends Controller
{
    private function resolveOutlet(string $code): Outlet
    {
        return Outlet::where('code', strtoupper($code))
            ->where('is_active', true)
            ->with(['outletType'])
            ->firstOrFail();
    }

    /** Cek apakah sesi bisnis F&B sudah terbuka (opening stok hari ini / kemarin, belum closing). */
    private function isOpeningDone(Outlet $outlet): bool
    {
        // Hanya akui opening dari hari ini atau kemarin (untuk operasi melewati tengah malam).
        // Opening dari hari-hari sebelumnya yang tidak pernah di-closing tidak dihitung.
        $cutoff = today()->subDay()->toDateString();

        $lastOpening = $outlet->stockOpnames()
            ->where('type', 'opening')
            ->where('date', '>=', $cutoff)
            ->orderByDesc('date')
            ->first();

        if (!$lastOpening) return false;

        $hasClosed = $outlet->dailyClosings()
            ->where('date', $lastOpening->date->toDateString())
            ->exists();

        return !$hasClosed;
    }

    public function index(string $code): View
    {
        $outlet = $this->resolveOutlet($code);

        // Pemesanan mandiri hanya untuk F&B
        if ($outlet->rp() !== 'fnb') {
            return view('public.menu.unavailable', compact('outlet'));
        }

        $requiresOpening  = $outlet->outletType?->requires_opening_stock ?? false;
        $openingDone      = !$requiresOpening || $this->isOpeningDone($outlet);
        $selfOrderEnabled = (bool) $outlet->enable_self_order;

        $categories = $outlet->categories()
            ->where('is_active', true)
            ->with(['products' => fn($q) => $q
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn($c) => $c->products->isNotEmpty());

        $uncategorized = $outlet->products()
            ->where('is_active', true)
            ->whereNull('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.menu.index', compact(
            'outlet', 'categories', 'uncategorized',
            'requiresOpening', 'openingDone', 'selfOrderEnabled'
        ));
    }

    public function placeOrder(Request $request, string $code): JsonResponse
    {
        $outlet = Outlet::where('code', strtoupper($code))
            ->where('is_active', true)
            ->with(['outletType'])
            ->firstOrFail();

        if ($outlet->rp() !== 'fnb') {
            return response()->json(['message' => 'Pemesanan mandiri hanya tersedia untuk outlet F&B.'], 403);
        }

        if (!$outlet->enable_self_order) {
            return response()->json(['message' => 'Fitur pemesanan mandiri dinonaktifkan oleh outlet.'], 403);
        }

        $requiresOpening = $outlet->outletType?->requires_opening_stock ?? false;

        if ($requiresOpening && !$this->isOpeningDone($outlet)) {
            return response()->json([
                'message' => 'Outlet belum membuka sesi hari ini. Pemesanan belum bisa dilakukan.',
            ], 422);
        }

        $data = $request->validate([
            'customer_name'      => ['required', 'string', 'max:100'],
            'table_number'       => ['nullable', 'string', 'max:30'],
            'notes'              => ['nullable', 'string', 'max:500'],
            'items'              => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $productIds = collect($data['items'])->pluck('product_id')->unique()->toArray();
        $products   = $outlet->products()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return response()->json(['message' => 'Produk tidak valid.'], 422);
        }

        $duplicate = Order::where('outlet_id', $outlet->id)
            ->where('customer_name', $data['customer_name'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->first();

        if ($duplicate) {
            return response()->json([
                'message'   => "Nama \"{$data['customer_name']}\" masih memiliki pesanan yang belum selesai. Gunakan nama lain, atau cek status pesanan sebelumnya.",
                'track_url' => route('public.menu.track', [$code, $duplicate->order_number]),
            ], 422);
        }

        // Validasi stok jika outlet wajib opening stok
        if ($requiresOpening) {
            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) continue;
                if ($product->stock < $item['qty']) {
                    return response()->json([
                        'message' => "Stok {$product->name} tidak mencukupi. Tersisa: {$product->stock}.",
                    ], 422);
                }
            }
        }

        $order = DB::transaction(function () use ($data, $outlet, $products) {
            // Kunci baris outlet (selalu ada) supaya request self-order yang masuk
            // bersamaan tidak ikut menghitung nomor urut sebelum yang pertama commit —
            // lockForUpdate() pada query orders tidak mengunci apa pun saat belum ada
            // order sama sekali hari itu, sehingga urutan pertama bisa duplikat.
            Outlet::whereKey($outlet->id)->lockForUpdate()->first();

            $orderNumber = $this->generateOrderNumber($outlet->id);
            $subtotal    = 0;
            $lines       = [];

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) continue;

                $lineTotal = $product->price * $item['qty'];
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_price' => $product->price,
                    'qty'           => $item['qty'],
                    'subtotal'      => $lineTotal,
                    'is_served'     => false,
                ];
            }

            $order = Order::create([
                'outlet_id'        => $outlet->id,
                'user_id'          => null,
                'order_number'     => $orderNumber,
                'table_number'     => $data['table_number'] ?? null,
                'customer_name'    => $data['customer_name'],
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'discount_percent' => 0,
                'discount_amount'  => 0,
                'total'            => $subtotal,
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                OrderItem::create(array_merge($line, ['order_id' => $order->id]));
            }

            return $order;
        });

        return response()->json([
            'order_number' => $order->order_number,
            'track_url'    => route('public.menu.track', [$code, $order->order_number]),
            'total'        => $order->total,
        ]);
    }

    public function track(string $code, string $orderNumber): View
    {
        $outlet = Outlet::where('code', strtoupper($code))->firstOrFail();
        $order  = Order::with('items')
            ->where('outlet_id', $outlet->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('public.menu.track', compact('outlet', 'order'));
    }

    private function generateOrderNumber(int $outletId): string
    {
        $prefix = 'SELF-' . now()->format('ymd') . '-';
        $last   = Order::where('outlet_id', $outletId)
            ->where('order_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('order_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
