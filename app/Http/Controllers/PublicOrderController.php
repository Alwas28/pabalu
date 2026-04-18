<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\OwnerSetting;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    public function show(string $slug): View
    {
        $outlet = Outlet::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $tanggal  = today()->toDateString();
        $products = Product::where('outlet_id', $outlet->id)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('nama')
            ->get()
            ->map(function ($p) use ($outlet, $tanggal) {
                $p->stok = StockMovement::currentStock($outlet->id, $p->id, $tanggal);
                return $p;
            })
            ->filter(fn($p) => $p->stok > 0)
            ->values();

        $categories = $products->pluck('category')->filter()->unique('id')->values();

        $ownerId = $outlet->owner_id;
        $paymentEnabled = $outlet->payment_gateway_enabled
            && Setting::get('midtrans_enabled') === '1'
            && OwnerSetting::get('midtrans_enabled', $ownerId) === '1'
            && OwnerSetting::get('midtrans_server_key', $ownerId);

        $midtransClientKey  = $paymentEnabled ? OwnerSetting::get('midtrans_client_key', $ownerId, '') : null;
        $midtransProduction = $paymentEnabled && OwnerSetting::get('midtrans_is_production', $ownerId) === '1';

        return view('order.show', compact(
            'outlet', 'products', 'categories',
            'paymentEnabled', 'midtransClientKey', 'midtransProduction'
        ));
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $outlet = Outlet::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name'  => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'catatan'        => ['nullable', 'string', 'max:500'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ]);

        $tanggal = today()->toDateString();
        $subtotal = 0;
        $orderLines = [];

        foreach ($validated['items'] as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('outlet_id', $outlet->id)
                ->where('is_active', true)
                ->firstOrFail();

            $stok = StockMovement::currentStock($outlet->id, $product->id, $tanggal);
            if ($item['qty'] > $stok) {
                return response()->json([
                    'message' => "Stok \"{$product->nama}\" tidak mencukupi (tersisa {$stok}).",
                ], 422);
            }

            $line = [
                'product_id'   => $product->id,
                'nama_produk'  => $product->nama,
                'harga_satuan' => $product->harga_jual,
                'qty'          => $item['qty'],
                'subtotal'     => $product->harga_jual * $item['qty'],
            ];
            $subtotal += $line['subtotal'];
            $orderLines[] = $line;
        }

        // Cek apakah outlet menggunakan payment gateway
        $ownerId    = $outlet->owner_id;
        $usePayment = $outlet->payment_gateway_enabled
            && Setting::get('midtrans_enabled') === '1'
            && OwnerSetting::get('midtrans_enabled', $ownerId) === '1'
            && OwnerSetting::get('midtrans_server_key', $ownerId);

        $order = Order::create([
            'outlet_id'      => $outlet->id,
            'order_number'   => Order::generateNumber($outlet->id),
            'customer_name'  => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'catatan'        => $validated['catatan'] ?? null,
            'subtotal'       => $subtotal,
            'order_status'   => $usePayment ? 'pending_payment' : 'pending',
        ]);

        foreach ($orderLines as $line) {
            $order->items()->create($line);
        }

        // Buat Snap token jika payment gateway aktif
        if ($usePayment) {
            try {
                \Midtrans\Config::$serverKey    = OwnerSetting::get('midtrans_server_key', $ownerId);
                \Midtrans\Config::$isProduction = OwnerSetting::get('midtrans_is_production', $ownerId) === '1';
                \Midtrans\Config::$isSanitized  = true;
                \Midtrans\Config::$is3ds        = true;

                $snapParams = [
                    'transaction_details' => [
                        'order_id'     => $order->order_number,
                        'gross_amount' => $order->subtotal,
                    ],
                    'customer_details' => [
                        'first_name' => $order->customer_name,
                        'phone'      => $order->customer_phone,
                    ],
                    'item_details' => $order->items->map(fn($item) => [
                        'id'       => (string) ($item->product_id ?? $item->id),
                        'price'    => $item->harga_satuan,
                        'quantity' => $item->qty,
                        'name'     => mb_substr($item->nama_produk, 0, 50),
                    ])->toArray(),
                ];

                $snapToken = \Midtrans\Snap::getSnapToken($snapParams);
                $order->update(['payment_token' => $snapToken]);

                return response()->json([
                    'success'       => true,
                    'order_number'  => $order->order_number,
                    'subtotal'      => $subtotal,
                    'payment_token' => $snapToken,
                ]);
            } catch (\Exception $e) {
                // Jika Snap gagal, batalkan order dan kembalikan error
                $order->delete();
                return response()->json([
                    'message' => 'Gagal membuat sesi pembayaran: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'subtotal'     => $subtotal,
        ]);
    }

    // Polling endpoint untuk cek status order oleh pelanggan
    public function status(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return response()->json([
            'order_status' => $order->order_status,
            'label'        => Order::statusLabel($order->order_status),
            'paid'         => (bool) $order->paid_at,
        ]);
    }
}
