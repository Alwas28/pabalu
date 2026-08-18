<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesOutlet;
use App\Http\Controllers\Concerns\EnforcesProFeature;
use App\Http\Controllers\Controller;
use App\Http\Resources\LaundryOrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Customer;
use App\Models\LaundryOrder;
use App\Models\LaundryOrderItem;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Laundry TIDAK memakai bentuk transaksi "keranjang -> checkout langsung" seperti
// Api\V1\PosController (Fnb/Retail/Salon). Alurnya bertahap: buat order (belum bayar,
// status "masuk") -> status berjalan (proses -> selesai) -> baru dibayar saat "selesai"
// (status jadi "diambil"). Cermin persis Laundry\LaundryOrderController (web).
class LaundryController extends Controller
{
    use AuthorizesOutlet, EnforcesProFeature;

    private function ensureSupported(Outlet $outlet): void
    {
        if ($outlet->rp() !== 'laundry') {
            abort(501, 'Endpoint ini hanya untuk outlet Laundry.');
        }
    }

    public function products(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        $products = $outlet->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => ProductResource::collection($products)]);
    }

    public function index(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        $status = $request->input('status', 'masuk');
        if (!in_array($status, ['masuk', 'proses', 'selesai', 'diambil'], true)) {
            $status = 'masuk';
        }

        $q = trim((string) $request->input('q', ''));

        $orders = LaundryOrder::where('outlet_id', $outlet->id)
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', '%' . $q . '%')
                        ->orWhere('customer_name', 'like', '%' . $q . '%')
                        ->orWhere('customer_phone', 'like', '%' . $q . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => LaundryOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Outlet $outlet, LaundryOrder $laundryOrder): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        if ($laundryOrder->outlet_id !== $outlet->id) {
            abort(404);
        }

        $laundryOrder->load('items');

        return response()->json(['data' => new LaundryOrderResource($laundryOrder)]);
    }

    public function store(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->ensureSupported($outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        $request->validate([
            'customer_id'           => ['nullable', 'integer'],
            'customer_name'         => ['required', 'string', 'max:120'],
            'customer_phone'        => ['nullable', 'string', 'max:30'],
            'save_customer'         => ['boolean'],
            'weight_kg'             => ['nullable', 'numeric', 'min:0', 'max:999'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'estimated_done_at'     => ['nullable', 'date'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['nullable', 'integer'],
            'items.*.product_name'  => ['required', 'string', 'max:200'],
            'items.*.product_price' => ['required', 'integer', 'min:0'],
            'items.*.qty'           => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['nullable', 'string', 'max:30'],
            'items.*.item_notes'    => ['nullable', 'string', 'max:300'],
        ]);

        // Tanpa fitur "Simpan Pelanggan Cepat", pesanan harus untuk pelanggan yang
        // sudah terdaftar (customer_id valid milik outlet ini).
        if (!$this->ownerHasProFeature($outlet, $user, 'laundry_simpan_pelanggan_cepat')) {
            $customerId    = $request->input('customer_id');
            $customerValid = $customerId && Customer::where('id', $customerId)->where('outlet_id', $outlet->id)->exists();

            if (!$customerValid) {
                return response()->json([
                    'message' => 'Paket Anda belum termasuk fitur "Simpan Pelanggan Cepat". Tambahkan pelanggan ini dulu di menu Pelanggan, baru bisa membuat pesanan.',
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($request, $outlet, $user) {
            $customerId = null;
            if ($request->filled('customer_id')) {
                $customerId = (int) $request->customer_id;
                Customer::where('id', $customerId)->where('outlet_id', $outlet->id)
                    ->update(['phone' => $request->customer_phone ?: null]);
            } elseif ($request->boolean('save_customer')) {
                $customer   = Customer::create([
                    'outlet_id' => $outlet->id,
                    'name'      => $request->customer_name,
                    'phone'     => $request->customer_phone ?: null,
                ]);
                $customerId = $customer->id;
            }

            $outletCode = strtoupper($outlet->code ?? 'LDR');
            $dateLabel  = now()->format('Ymd');
            $todayCount = LaundryOrder::where('outlet_id', $outlet->id)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->count();
            $orderNumber = 'LDR-' . $outletCode . '-' . $dateLabel . '-' . str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $subtotal  = 0;
            $itemsData = [];
            foreach ($request->items as $item) {
                $qty       = (float) $item['qty'];
                $price     = (int) $item['product_price'];
                $lineTotal = (int) round($price * $qty);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id'    => $item['product_id'] ?? null,
                    'product_name'  => $item['product_name'],
                    'product_price' => $price,
                    'qty'           => $qty,
                    'unit'          => $item['unit'] ?? null,
                    'subtotal'      => $lineTotal,
                    'item_notes'    => $item['item_notes'] ?? null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            $estimatedDoneAt = $request->estimated_done_at ? Carbon::parse($request->estimated_done_at) : null;

            $order = LaundryOrder::create([
                'outlet_id'         => $outlet->id,
                'user_id'           => $user->id,
                'customer_id'       => $customerId,
                'order_number'      => $orderNumber,
                'customer_name'     => $request->customer_name,
                'customer_phone'    => $request->customer_phone,
                'weight_kg'         => $request->weight_kg,
                'notes'             => $request->notes,
                'estimated_done_at' => $estimatedDoneAt,
                'status'            => 'masuk',
                'subtotal'          => $subtotal,
                'discount_amount'   => 0,
                'total'             => $subtotal,
            ]);

            foreach ($itemsData as &$it) {
                $it['laundry_order_id'] = $order->id;
            }
            unset($it);

            LaundryOrderItem::insert($itemsData);

            return $order;
        });

        $order->load('items');

        return response()->json(['data' => new LaundryOrderResource($order)], 201);
    }

    public function updateStatus(Request $request, Outlet $outlet, LaundryOrder $laundryOrder): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        if ($laundryOrder->outlet_id !== $outlet->id) {
            abort(404);
        }

        $next = $laundryOrder->nextStatus();
        if (!$next) {
            return response()->json(['message' => 'Pesanan sudah di status akhir.'], 422);
        }

        $laundryOrder->update(['status' => $next]);

        return response()->json(['data' => new LaundryOrderResource($laundryOrder->fresh())]);
    }

    public function pay(Request $request, Outlet $outlet, LaundryOrder $laundryOrder): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $this->requireProFeature($outlet, $user, 'laundry_akses_mobile', 'Akses Aplikasi Kasir Mobile');

        if ($laundryOrder->outlet_id !== $outlet->id) {
            abort(404);
        }

        if ($laundryOrder->status !== 'selesai') {
            return response()->json(['message' => 'Pesanan harus berstatus Selesai sebelum dibayar.'], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string'],
            'payment_amount' => ['required', 'integer', 'min:0'],
        ]);

        $paymentAmount = (int) $data['payment_amount'];
        $changeAmount  = max(0, $paymentAmount - $laundryOrder->total);

        DB::transaction(function () use ($outlet, $laundryOrder, $user, $data, $paymentAmount, $changeAmount) {
            $laundryOrder->update([
                'status'         => 'diambil',
                'payment_method' => $data['payment_method'],
                'payment_amount' => $paymentAmount,
                'change_amount'  => $changeAmount,
                'paid_at'        => now(),
            ]);

            $this->recordTransaction($outlet, $laundryOrder, $user);
        });

        return response()->json(['data' => new LaundryOrderResource($laundryOrder->fresh('items'))]);
    }

    // Cermin LaundryOrderController::recordTransaction() (web) — mencatat ke tabel
    // `transactions` supaya konsisten dengan Riwayat/Dashboard yang sudah dibangun.
    private function recordTransaction(Outlet $outlet, LaundryOrder $laundryOrder, User $user): Transaction
    {
        $laundryOrder->loadMissing('items');
        $outlet->loadMissing('outletType');

        $typeCode     = $outlet->outletType?->type_code ?? '08';
        $outletCode   = $outlet->code ?? 'XXXX';
        $businessDate = today()->format('Y-m-d');
        $dateLabel    = now()->format('ymd');
        $timeLabel    = now()->format('His');

        $dailyCount = Transaction::where('outlet_id', $outlet->id)
            ->where('date', $businessDate)
            ->lockForUpdate()
            ->count();

        $txNumber = 'TRXPB' . $typeCode . $outletCode . $dateLabel . $timeLabel
            . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

        $transaction = Transaction::create([
            'outlet_id'          => $outlet->id,
            'laundry_order_id'   => $laundryOrder->id,
            'user_id'            => $laundryOrder->user_id ?? $user->id,
            'transaction_number' => $txNumber,
            'date'               => $businessDate,
            'subtotal'           => $laundryOrder->subtotal,
            'discount_percent'   => 0,
            'discount_amount'    => $laundryOrder->discount_amount,
            'total'              => $laundryOrder->total,
            'payment_method'     => $laundryOrder->payment_method,
            'payment_amount'     => $laundryOrder->payment_amount,
            'change_amount'      => $laundryOrder->change_amount,
            'status'             => 'completed',
            'notes'              => $laundryOrder->notes,
        ]);

        foreach ($laundryOrder->items as $item) {
            $transaction->items()->create([
                'product_id'    => $item->product_id,
                'product_name'  => $item->product_name . ($item->unit ? " ({$item->unit})" : ''),
                'product_price' => $item->product_price,
                'qty'           => max(1, (int) ceil($item->qty)),
                'subtotal'      => $item->subtotal,
            ]);
        }

        return $transaction;
    }
}
