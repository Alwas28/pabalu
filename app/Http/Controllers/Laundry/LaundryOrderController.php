<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LaundryOrder;
use App\Models\Outlet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaundryOrderController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$outlet->isAccessibleBy($user)) abort(403);
        return $user;
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        $status = $request->input('status', 'masuk');
        $validStatuses = ['masuk', 'proses', 'selesai', 'diambil'];
        if (!in_array($status, $validStatuses)) $status = 'masuk';

        $q = trim((string) $request->input('q', ''));

        $orders = LaundryOrder::where('outlet_id', $outlet->id)
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number',   'like', '%' . $q . '%')
                        ->orWhere('customer_name', 'like', '%' . $q . '%')
                        ->orWhere('customer_phone','like', '%' . $q . '%');
                });
            })
            ->with(['user', 'items'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = LaundryOrder::where('outlet_id', $outlet->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $products = $outlet->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'unit']);

        return view('laundry.laundry-orders.index', compact(
            'outlet', 'user', 'orders', 'counts', 'products', 'status', 'q'
        ));
    }

    public function store(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($outlet);

        $request->validate([
            'customer_id'      => ['nullable', 'integer'],
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_phone'   => ['nullable', 'string', 'max:30'],
            'save_customer'    => ['boolean'],
            'weight_kg'        => ['nullable', 'numeric', 'min:0', 'max:999'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'estimated_done_at'=> ['nullable', 'date'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['nullable', 'integer'],
            'items.*.product_name' => ['required', 'string', 'max:200'],
            'items.*.product_price'=> ['required', 'integer', 'min:0'],
            'items.*.qty'          => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'         => ['nullable', 'string', 'max:30'],
            'items.*.item_notes'   => ['nullable', 'string', 'max:300'],
        ]);

        /** @var LaundryOrder $order */
        $order = DB::transaction(function () use ($request, $outlet, $user) {
            // Resolve atau buat customer
            $customerId = null;
            if ($request->filled('customer_id')) {
                $customerId = (int) $request->customer_id;
                // Update phone jika berubah
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

            // Generate order number: LDR-{code}-{YYYYMMDD}-{SEQ:03}
            $outletCode = strtoupper($outlet->code ?? 'LDR');
            $dateLabel  = now()->format('Ymd');
            $todayCount = LaundryOrder::where('outlet_id', $outlet->id)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->count();
            $orderNumber = 'LDR-' . $outletCode . '-' . $dateLabel . '-' . str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $itemsData = [];
            foreach ($request->items as $item) {
                $qty      = (float) $item['qty'];
                $price    = (int) $item['product_price'];
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

            $estimatedDoneAt = $request->estimated_done_at
                ? Carbon::parse($request->estimated_done_at)
                : null;

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

            \App\Models\LaundryOrderItem::insert($itemsData);

            return $order;
        });

        return response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'show_url'     => $outlet->route('laundry-orders.show', [$order->id]),
            'receipt_url'  => $outlet->route('laundry-orders.receipt', [$order->id]),
        ]);
    }

    public function show(Outlet $outlet, LaundryOrder $laundryOrder): View
    {
        $user = $this->authorizeOutlet($outlet);

        if ($laundryOrder->outlet_id !== $outlet->id) abort(404);

        $laundryOrder->load(['items', 'user']);

        $products = $outlet->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'unit']);

        return view('laundry.laundry-orders.show', compact(
            'outlet', 'user', 'laundryOrder', 'products'
        ));
    }

    public function updateStatus(Request $request, Outlet $outlet, LaundryOrder $laundryOrder): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        if ($laundryOrder->outlet_id !== $outlet->id) abort(404);

        $next = $laundryOrder->nextStatus();
        if (!$next) {
            return response()->json(['message' => 'Pesanan sudah di status akhir.'], 422);
        }

        $laundryOrder->update(['status' => $next]);

        return response()->json([
            'success'      => true,
            'status'       => $next,
            'status_label' => LaundryOrder::$statusLabels[$next],
        ]);
    }

    public function pay(Request $request, Outlet $outlet, LaundryOrder $laundryOrder): JsonResponse
    {
        $this->authorizeOutlet($outlet);

        if ($laundryOrder->outlet_id !== $outlet->id) abort(404);

        if ($laundryOrder->status !== 'selesai') {
            return response()->json(['message' => 'Pesanan harus berstatus Selesai sebelum dibayar.'], 422);
        }

        $request->validate([
            'payment_method' => ['required', 'string'],
            'payment_amount' => ['required', 'integer', 'min:0'],
        ]);

        $paymentAmount = (int) $request->payment_amount;
        $changeAmount  = max(0, $paymentAmount - $laundryOrder->total);

        $laundryOrder->update([
            'status'         => 'diambil',
            'payment_method' => $request->payment_method,
            'payment_amount' => $paymentAmount,
            'change_amount'  => $changeAmount,
            'paid_at'        => now(),
        ]);

        return response()->json([
            'success'       => true,
            'change_amount' => $changeAmount,
            'receipt_url'   => $outlet->route('laundry-orders.receipt', [$laundryOrder->id]),
        ]);
    }

    public function receipt(Outlet $outlet, LaundryOrder $laundryOrder): View
    {
        $user = $this->authorizeOutlet($outlet);

        if ($laundryOrder->outlet_id !== $outlet->id) abort(404);

        $laundryOrder->load(['items', 'user']);

        return view('laundry.laundry-orders.receipt', compact(
            'outlet', 'laundryOrder'
        ));
    }

    public function destroy(Outlet $outlet, LaundryOrder $laundryOrder): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if ($laundryOrder->outlet_id !== $outlet->id) abort(404);

        if ($laundryOrder->status === 'diambil') {
            return back()->with('error', 'Pesanan yang sudah diambil tidak dapat dihapus.');
        }

        $laundryOrder->delete();

        return redirect($outlet->route('laundry-orders.index'))
            ->with('success', 'Pesanan #' . $laundryOrder->order_number . ' berhasil dihapus.');
    }
}
