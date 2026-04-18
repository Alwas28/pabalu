<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderQueueController extends Controller
{
    public function index(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $statusFilter     = $request->get('status', 'active'); // active = pending+processing+ready

        $query = Order::with('items')->latest();

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        if ($statusFilter === 'active') {
            $query->whereIn('order_status', ['pending', 'processing', 'ready']);
        } elseif ($statusFilter !== 'all') {
            $query->where('order_status', $statusFilter);
        }

        $orders = $query->get();

        $stats = [
            'pending'    => Order::when($outletId, fn($q) => $q->where('outlet_id', $outletId))->where('order_status', 'pending')->count(),
            'processing' => Order::when($outletId, fn($q) => $q->where('outlet_id', $outletId))->where('order_status', 'processing')->count(),
            'ready'      => Order::when($outletId, fn($q) => $q->where('outlet_id', $outletId))->where('order_status', 'ready')->count(),
        ];

        return view('orders.queue', compact('orders', 'outlets', 'outletId', 'assignedOutletId', 'statusFilter', 'stats'));
    }

    public function advance(Request $request, Order $order): RedirectResponse
    {
        $next = $order->nextStatus();

        if (! $next) {
            return back()->with('error', 'Status order tidak dapat diubah.');
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

        return back()->with('success', "Order {$order->order_number} → " . Order::statusLabel($next));
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Order sudah selesai atau dibatalkan.');
        }

        $order->update(['order_status' => 'cancelled']);

        return back()->with('success', "Order {$order->order_number} dibatalkan.");
    }

    // Polling endpoint — cek apakah ada order baru
    public function poll(Request $request): JsonResponse
    {
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id');

        $since = $request->get('since'); // ISO timestamp dari client

        $query = Order::where('order_status', 'pending');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $newCount = $query->count();
        $pending  = Order::when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->whereIn('order_status', ['pending', 'processing', 'ready'])
            ->count();

        return response()->json([
            'new_count' => $newCount,
            'pending'   => $pending,
            'now'       => now()->toISOString(),
        ]);
    }
}
