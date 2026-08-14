<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesOutlet;
use App\Http\Controllers\Concerns\ResolvesBusinessDate;
use App\Http\Controllers\Controller;
use App\Http\Resources\OutletResource;
use App\Http\Resources\ShiftResource;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use AuthorizesOutlet, ResolvesBusinessDate;

    // Ringkasan untuk tab Home/Dashboard mobile — data hari ini milik user yang login
    // (bukan seluruh outlet), supaya konsisten dengan scoping "riwayat transaksi milik saya"
    // yang sudah dipakai di PosController::index().
    public function show(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);
        $outlet->loadMissing('outletType');

        $businessDate = $this->getBusinessDate($outlet);

        $activeShift = $outlet->shifts()
            ->where('user_id', $user->id)
            ->whereNull('closed_at')
            ->first();

        $txBase = $outlet->transactions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('date', $businessDate);

        $totalTransactions = (clone $txBase)->count();
        $totalRevenue      = (int) (clone $txBase)->sum('total');

        $topProducts = \App\Models\TransactionItem::query()
            ->select('product_name')
            ->selectRaw('SUM(qty) as total_qty')
            ->whereHas('transaction', function ($q) use ($outlet, $user, $businessDate) {
                $q->where('outlet_id', $outlet->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('date', $businessDate);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['name' => $row->product_name, 'qty' => (int) $row->total_qty]);

        return response()->json([
            'outlet'        => new OutletResource($outlet),
            'business_date' => $businessDate,
            'shift'         => [
                'enabled'      => (bool) $outlet->enable_opening_shift,
                'active_shift' => $activeShift ? new ShiftResource($activeShift) : null,
            ],
            'today' => [
                'total_transactions' => $totalTransactions,
                'total_revenue'      => $totalRevenue,
                'top_products'       => $topProducts,
            ],
        ]);
    }
}
