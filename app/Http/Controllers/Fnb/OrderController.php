<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\RequiresActiveShift;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    use RequiresActiveShift;

    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        return $user;
    }

    // â”€â”€ Antrian aktif â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $activeOrders = $outlet->orders()
            ->with(['user', 'items'])
            ->whereIn('status', ['pending', 'open', 'preparing', 'served'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        $todayPaid = $outlet->orders()
            ->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->count();

        $selfOrderEnabled = (bool) $outlet->enable_self_order;

        return view('fnb.orders.index', compact('outlet', 'user', 'activeOrders', 'todayPaid', 'selfOrderEnabled'));
    }

    // â”€â”€ Buat order baru (dari POS kitchen mode, via JSON) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function store(Request $request, Outlet $outlet): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$user->hasPermission('pos.access')) {
            return response()->json(['message' => 'Tidak punya izin.'], 403);
        }

        if ($this->shiftRequired($outlet, $user)) {
            return response()->json([
                'message' => 'Shift belum dibuka. Buka shift terlebih dahulu sebelum melakukan transaksi.',
            ], 422);
        }

        $data = $request->validate([
            'table_number'    => ['nullable', 'string', 'max:30'],
            'customer_name'   => ['nullable', 'string', 'max:100'],
            'discount_type'   => ['nullable', 'in:percent,nominal,amount'],
            'discount_value'  => ['nullable', 'integer', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.id'      => ['required', 'integer'],
            'items.*.qty'     => ['required', 'integer', 'min:1'],
        ]);

        // Validate each product
        $validated = [];
        foreach ($data['items'] as $item) {
            $product = $outlet->products()
                ->where('id', $item['id'])
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json(['message' => "Produk ID {$item['id']} tidak ditemukan."], 422);
            }

            $validated[] = ['product' => $product, 'qty' => $item['qty'], 'subtotal' => $product->price * $item['qty']];
        }

        $order = DB::transaction(function () use ($outlet, $user, $data, $validated) {
            $subtotal    = collect($validated)->sum('subtotal');
            $discType    = $data['discount_type'] ?? 'percent';
            $discVal     = (int) ($data['discount_value'] ?? 0);
            $discPct     = 0;
            $discAmt     = 0;

            if ($discVal > 0) {
                if ($discType === 'percent') {
                    $discPct = min($discVal, 100);
                    $discAmt = (int) round($subtotal * $discPct / 100);
                } else { // nominal or amount
                    $discAmt = min($discVal, $subtotal);
                }
            }

            $total = $subtotal - $discAmt;

            // Order number: ORDPB{typeCode}{outletCode}{YYMMDD}{SEQ:03}
            $outlet->loadMissing('outletType');
            $typeCode   = $outlet->outletType?->type_code ?? '00';
            $outletCode = $outlet->code ?? 'XXXX';
            $dateLabel  = now()->format('ymd');
            $todayCount = Order::where('outlet_id', $outlet->id)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->count();

            $orderNumber = 'ORDPB' . $typeCode . $outletCode . $dateLabel . str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'outlet_id'       => $outlet->id,
                'user_id'         => $user->id,
                'order_number'    => $orderNumber,
                'table_number'    => $data['table_number'] ?? null,
                'customer_name'   => $data['customer_name'] ?? null,
                'status'          => 'open',
                'subtotal'        => $subtotal,
                'discount_percent'=> $discPct,
                'discount_amount' => $discAmt,
                'total'           => $total,
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($validated as $v) {
                $order->items()->create([
                    'product_id'    => $v['product']->id,
                    'product_name'  => $v['product']->name,
                    'product_price' => $v['product']->price,
                    'qty'           => $v['qty'],
                    'subtotal'      => $v['subtotal'],
                ]);
                // Decrement stock at order time
                $v['product']->decrement('stock', $v['qty']);
            }

            return $order;
        });

        $kitchenUrl = $outlet->route('orders.kitchen', [$order]) . '?autoprint=1';
        $orderUrl   = $outlet->route('orders.show', [$order]);

        return response()->json([
            'success'       => true,
            'order_number'  => $order->order_number,
            'table'         => $order->table_number,
            'customer_name' => $order->customer_name,
            'kitchen_url'   => $kitchenUrl,
            'order_url'     => $orderUrl,
        ]);
    }

    // â”€â”€ Detail order + checklist â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function show(Outlet $outlet, Order $order): View
    {
        $user = $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $outlet->load('outletType');
        $order->load(['items.product', 'user', 'transactions']);

        $mergeable = Order::where('outlet_id', $outlet->id)
            ->where('id', '!=', $order->id)
            ->whereIn('status', ['open', 'preparing', 'served'])
            ->when($outlet->isKitchenMode(), fn ($q) => $q->where('status', 'served'))
            ->with('items')
            ->orderBy('created_at')
            ->get();

        $paymentMethods = $outlet->activePaymentMethods();

        return view('fnb.orders.show', compact('outlet', 'user', 'order', 'mergeable', 'paymentMethods'));
    }

    // â”€â”€ Checklist item diantarkan â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function serveItem(Outlet $outlet, Order $order, int $itemId): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $item = $order->items()->findOrFail($itemId);
        $item->update(['is_served' => !$item->is_served]);

        // Auto-upgrade status to 'served' if all items served
        $order->load('items');
        if ($order->allServed() && $order->status === 'preparing') {
            $order->update(['status' => 'served']);
        }

        return response()->json([
            'is_served'  => $item->is_served,
            'all_served' => $order->allServed(),
            'status'     => $order->fresh()->status,
        ]);
    }

    // â”€â”€ Update status order â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function updateStatus(Request $request, Outlet $outlet, Order $order): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $request->validate(['status' => ['required', 'in:open,preparing,served,cancelled']]);

        if (!in_array($order->status, ['pending', 'open', 'preparing', 'served'])) {
            return back()->with('error', 'Status order tidak bisa diubah.');
        }

        // pending hanya boleh → open (konfirmasi) atau cancelled (tolak)
        if ($order->status === 'pending' && !in_array($request->status, ['open', 'cancelled'])) {
            return back()->with('error', 'Pesanan masuk hanya bisa dikonfirmasi atau ditolak.');
        }

        if ($request->status === 'cancelled') {
            if (!in_array($order->status, ['pending', 'open'])) {
                return back()->with('error', 'Order yang sedang diproses atau sudah diantarkan tidak bisa dibatalkan.');
            }

            $wasPending = $order->status === 'pending';

            DB::transaction(function () use ($order, $wasPending) {
                // Stok hanya dikembalikan untuk order dari POS (open),
                // bukan self-order (pending) karena stok belum dikurangkan.
                if (!$wasPending) {
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $item->product?->increment('stock', $item->qty);
                        }
                    }
                }
                $order->update(['status' => 'cancelled']);
            });

            $msg = $wasPending ? 'Pesanan ditolak.' : 'Order dibatalkan dan stok telah dikembalikan.';
            return back()->with('success', $msg);
        }

        $order->update(['status' => $request->status]);
        $msg = $request->status === 'open' ? 'Pesanan dikonfirmasi.' : 'Status order diperbarui.';
        return back()->with('success', $msg);
    }

    // â”€â”€ Validasi order(s) yang akan digabung dalam satu pembayaran â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    /** @return array{0: ?\Illuminate\Support\Collection, 1: int, 2: ?string} [$allOrders, $combinedTotal, $errorMessage] */
    private function resolveMergeOrders(Outlet $outlet, Order $order, array $mergeIds): array
    {
        $mergeIds    = array_unique($mergeIds);
        $mergeOrders = collect();

        if (!empty($mergeIds)) {
            $mergeOrders = Order::where('outlet_id', $outlet->id)
                ->where('id', '!=', $order->id)
                ->whereIn('id', $mergeIds)
                ->with('items')
                ->get();

            if ($mergeOrders->count() !== count($mergeIds)) {
                return [null, 0, 'Salah satu order yang digabung tidak ditemukan.'];
            }

            foreach ($mergeOrders as $mo) {
                if (!in_array($mo->status, ['open', 'preparing', 'served'])) {
                    return [null, 0, "Order {$mo->order_number} sudah lunas/dibatalkan dan tidak bisa digabung."];
                }
                if ($outlet->isKitchenMode() && $mo->status !== 'served') {
                    return [null, 0, "Order {$mo->order_number} belum diantarkan, tidak bisa digabung."];
                }
            }
        }

        $order->loadMissing('items');
        $allOrders = $mergeOrders->prepend($order);

        return [$allOrders, (int) $allOrders->sum('total'), null];
    }

    // â”€â”€ Buat transaksi lunas + tandai semua order yang terlibat sebagai paid â”€â”€
    private function createTransactionRecord(Outlet $outlet, Order $order, $allOrders, int $combinedTotal, array $paymentData, User $user): Transaction
    {
        return DB::transaction(function () use ($outlet, $order, $allOrders, $combinedTotal, $paymentData, $user) {
            $outlet->loadMissing('outletType');
            $typeCode     = $outlet->outletType?->type_code ?? '00';
            $outletCode   = $outlet->code ?? 'XXXX';
            $businessDate = today()->format('Y-m-d');
            $dateLabel    = now()->format('ymd');
            $timeLabel    = now()->format('His');
            $dailyCount   = Transaction::where('outlet_id', $outlet->id)
                ->where('date', $businessDate)
                ->lockForUpdate()
                ->count();

            $txNumber = 'TRXPB' . $typeCode . $outletCode . $dateLabel . $timeLabel . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

            $transaction = Transaction::create(array_merge([
                'outlet_id'          => $outlet->id,
                'order_id'           => $order->id,
                'user_id'            => $user->id,
                'transaction_number' => $txNumber,
                'date'               => $businessDate,
                'subtotal'           => $allOrders->sum('subtotal'),
                'discount_percent'   => $allOrders->count() === 1 ? $order->discount_percent : 0,
                'discount_amount'    => $allOrders->sum('discount_amount'),
                'total'              => $combinedTotal,
                'status'             => 'completed',
                'notes'              => $order->notes,
            ], $paymentData));

            foreach ($allOrders as $o) {
                foreach ($o->items as $item) {
                    $transaction->items()->create([
                        'order_id'      => $o->id,
                        'product_id'    => $item->product_id,
                        'product_name'  => $item->product_name,
                        'product_price' => $item->product_price,
                        'qty'           => $item->qty,
                        'subtotal'      => $item->subtotal,
                    ]);
                }

                $transaction->orders()->attach($o->id);
                $o->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return $transaction;
        });
    }

    // â”€â”€ Proses pembayaran (tunai / QRIS Transfer / Transfer / Kartu) â”€â”€â”€â”€â”€â”€â”€â”€
    public function pay(Request $request, Outlet $outlet, Order $order): JsonResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        if ($this->shiftRequired($outlet, $user)) {
            return response()->json(['message' => 'Shift belum dibuka. Buka shift terlebih dahulu sebelum memproses pembayaran.'], 422);
        }

        if (!in_array($order->status, ['open', 'preparing', 'served'])) {
            return response()->json(['message' => 'Order ini sudah lunas atau dibatalkan.'], 422);
        }

        if ($outlet->isKitchenMode() && $order->status !== 'served') {
            return response()->json(['message' => 'Pembayaran hanya bisa dilakukan setelah semua pesanan diantarkan.'], 422);
        }

        $data = $request->validate([
            'payment_method'    => ['required', 'in:cash,qris_transfer,transfer,card'],
            'payment_amount'    => ['required', 'integer', 'min:0'],
            'reference_number'  => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:100'],
            'proof_image'       => ['required_unless:payment_method,cash', 'nullable', 'image', 'max:2048'],
            'merge_order_ids'   => ['nullable', 'array'],
            'merge_order_ids.*' => ['integer'],
        ]);

        $methodEnabledMap = [
            'qris_transfer' => $outlet->enable_qris_transfer,
            'transfer'      => $outlet->enable_transfer,
            'card'          => $outlet->enable_card,
        ];
        if (isset($methodEnabledMap[$data['payment_method']]) && !$methodEnabledMap[$data['payment_method']]) {
            return response()->json(['message' => 'Metode pembayaran ini tidak diaktifkan untuk outlet ini.'], 422);
        }

        [$allOrders, $combinedTotal, $error] = $this->resolveMergeOrders($outlet, $order, $data['merge_order_ids'] ?? []);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $paymentAmount = (int) $data['payment_amount'];
        if ($paymentAmount < $combinedTotal) {
            return response()->json(['message' => 'Jumlah pembayaran kurang dari total gabungan.'], 422);
        }

        $proofPath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('proof-transaksi', 'public')
            : null;

        $transaction = $this->createTransactionRecord($outlet, $order, $allOrders, $combinedTotal, [
            'payment_method'   => $data['payment_method'],
            'payment_amount'   => $paymentAmount,
            'change_amount'    => max(0, $paymentAmount - $combinedTotal),
            'reference_number' => $data['reference_number'] ?? null,
            'proof_image'      => $proofPath,
        ], $user);

        return response()->json([
            'success'     => true,
            'receipt_url' => $outlet->route('transactions.receipt', [$transaction]),
        ]);
    }

    // â”€â”€ QRIS Pay: buat tagihan QRIS dinamis via Midtrans â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function qrisCharge(Request $request, Outlet $outlet, Order $order): JsonResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        if (!$outlet->enable_qris_pay) {
            return response()->json(['message' => 'QRIS Pay tidak diaktifkan untuk outlet ini.'], 422);
        }
        if (!$outlet->isMidtransConfigured()) {
            return response()->json(['message' => 'Akun Midtrans belum dikonfigurasi. Hubungi pemilik outlet.'], 422);
        }
        if ($this->shiftRequired($outlet, $user)) {
            return response()->json(['message' => 'Shift belum dibuka. Buka shift terlebih dahulu sebelum memproses pembayaran.'], 422);
        }
        if (!in_array($order->status, ['open', 'preparing', 'served'])) {
            return response()->json(['message' => 'Order ini sudah lunas atau dibatalkan.'], 422);
        }
        if ($outlet->isKitchenMode() && $order->status !== 'served') {
            return response()->json(['message' => 'Pembayaran hanya bisa dilakukan setelah semua pesanan diantarkan.'], 422);
        }

        $data = $request->validate([
            'merge_order_ids'   => ['nullable', 'array'],
            'merge_order_ids.*' => ['integer'],
        ]);

        [, $combinedTotal, $error] = $this->resolveMergeOrders($outlet, $order, $data['merge_order_ids'] ?? []);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $midtransOrderId = 'QP' . $order->id . now()->format('YmdHisv');

        $result = (new MidtransService($outlet))->chargeQris($midtransOrderId, $combinedTotal);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'success'           => true,
            'midtrans_order_id' => $midtransOrderId,
            'qr_url'            => $result['qr_url'],
            'total'             => $combinedTotal,
        ]);
    }

    // â”€â”€ QRIS Pay: dipoll dari browser kasir untuk cek status pembayaran â”€â”€â”€â”€â”€
    public function qrisStatus(Request $request, Outlet $outlet, Order $order): JsonResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $data = $request->validate([
            'midtrans_order_id' => ['required', 'string'],
            'merge_order_ids'   => ['nullable', 'array'],
            'merge_order_ids.*' => ['integer'],
        ]);

        $existing = Transaction::where('midtrans_order_id', $data['midtrans_order_id'])->first();
        if ($existing) {
            return response()->json(['status' => 'paid', 'receipt_url' => $outlet->route('transactions.receipt', [$existing])]);
        }

        $status   = (new MidtransService($outlet))->checkStatus($data['midtrans_order_id']);
        $txStatus = $status['transaction_status'] ?? null;

        if (!in_array($txStatus, ['settlement', 'capture'])) {
            return response()->json(['status' => $txStatus ?? 'pending']);
        }

        [$allOrders, $combinedTotal, $error] = $this->resolveMergeOrders($outlet, $order, $data['merge_order_ids'] ?? []);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        try {
            $transaction = $this->createTransactionRecord($outlet, $order, $allOrders, $combinedTotal, [
                'payment_method'    => 'qris_pay',
                'payment_amount'    => $combinedTotal,
                'change_amount'     => 0,
                'midtrans_order_id' => $data['midtrans_order_id'],
            ], $user);
        } catch (QueryException $e) {
            $transaction = Transaction::where('midtrans_order_id', $data['midtrans_order_id'])->first();
            if (!$transaction) throw $e;
        }

        return response()->json([
            'status'      => 'paid',
            'receipt_url' => $outlet->route('transactions.receipt', [$transaction]),
        ]);
    }

    // â”€â”€ Struk dapur (print) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function kitchen(Outlet $outlet, Order $order): View
    {
        $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $outlet->load('outletType');
        $order->load(['items', 'user']);

        return view('fnb.orders.kitchen', compact('outlet', 'order'));
    }

    // Struk pesanan untuk kasir (list + harga + QR tracking)
    public function bill(Outlet $outlet, Order $order): View
    {
        $this->authorizeOutlet($outlet);

        if ($order->outlet_id !== $outlet->id) abort(404);

        $outlet->load('outletType');
        $order->load(['items', 'user']);

        $trackUrl = route('public.menu.track', [$outlet->code, $order->order_number]);

        return view('fnb.orders.bill', compact('outlet', 'order', 'trackUrl'));
    }

    // Cari order berdasarkan order_number (hasil scan QR struk) untuk kasir
    public function lookup(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        $orderNumber = (string) $request->query('order_number');

        $order = Order::where('outlet_id', $outlet->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        return response()->json([
            'redirect_url' => $outlet->route('orders.show', [$order]),
        ]);
    }
}
