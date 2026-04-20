<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    private function checkOutletAccess(Request $request, int $outletId): ?JsonResponse
    {
        $ok = $request->user()->accessibleOutlets()->where('id', $outletId)->exists();
        return $ok ? null : response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
    }

    // ── Daftar Order ─────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'status'    => ['nullable', 'in:active,all,pending,processing,ready,completed,cancelled'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $statusFilter = $request->status ?? 'active';

        $orders = Order::with('items')
            ->where('outlet_id', $request->outlet_id)
            ->when($statusFilter === 'active',
                fn($q) => $q->whereIn('order_status', ['pending', 'processing', 'ready']),
                fn($q) => $statusFilter !== 'all'
                    ? $q->where('order_status', $statusFilter)
                    : $q
            )
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        $stats = [
            'pending'    => Order::where('outlet_id', $request->outlet_id)->where('order_status', 'pending')->count(),
            'processing' => Order::where('outlet_id', $request->outlet_id)->where('order_status', 'processing')->count(),
            'ready'      => Order::where('outlet_id', $request->outlet_id)->where('order_status', 'ready')->count(),
        ];

        return response()->json(['stats' => $stats, 'orders' => $orders]);
    }

    // ── Advance Status ────────────────────────────────────
    public function advance(Request $request, Order $order): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $order->outlet_id)) return $err;

        $next = $order->nextStatus();
        if (! $next) {
            return response()->json(['message' => 'Status order tidak dapat diubah.'], 422);
        }

        $timestamps = [
            'processing' => ['processed_at' => now()],
            'ready'      => ['ready_at'     => now()],
            'completed'  => ['completed_at' => now()],
        ];

        $order->update(array_merge(
            ['order_status' => $next],
            $timestamps[$next] ?? []
        ));

        return response()->json([
            'message' => "Order {$order->order_number} → " . Order::statusLabel($next),
            'order'   => $this->formatOrder($order->fresh()),
        ]);
    }

    // ── Batalkan Order ────────────────────────────────────
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $order->outlet_id)) return $err;

        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'Order sudah selesai atau dibatalkan.'], 422);
        }

        $order->update(['order_status' => 'cancelled']);

        return response()->json(['message' => "Order {$order->order_number} dibatalkan."]);
    }

    // ── Polling ───────────────────────────────────────────
    public function poll(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'since'     => ['nullable', 'date'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $newCount = Order::where('outlet_id', $request->outlet_id)
            ->where('order_status', 'pending')
            ->when($request->since, fn($q) => $q->where('created_at', '>', $request->since))
            ->count();

        $pending = Order::where('outlet_id', $request->outlet_id)
            ->whereIn('order_status', ['pending', 'processing', 'ready'])
            ->count();

        return response()->json([
            'new_count' => $newCount,
            'pending'   => $pending,
            'now'       => now()->toISOString(),
        ]);
    }

    private function formatOrder(Order $o): array
    {
        return [
            'id'            => $o->id,
            'order_number'  => $o->order_number,
            'customer_name' => $o->customer_name,
            'customer_phone'=> $o->customer_phone,
            'catatan'       => $o->catatan,
            'subtotal'      => $o->subtotal,
            'order_status'  => $o->order_status,
            'status_label'  => Order::statusLabel($o->order_status),
            'next_status'   => $o->nextStatus(),
            'next_label'    => $o->nextLabel(),
            'created_at'    => $o->created_at?->toISOString(),
            'items'         => $o->items?->map(fn($i) => [
                'id'          => $i->id,
                'nama_produk' => $i->nama_produk,
                'qty'         => $i->qty,
                'harga_satuan'=> (int) $i->harga_satuan,
                'subtotal'    => (int) $i->subtotal,
            ]),
        ];
    }
}
