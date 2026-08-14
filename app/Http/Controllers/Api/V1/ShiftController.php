<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesOutlet;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Outlet;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use AuthorizesOutlet;

    // Status shift milik user yang login untuk outlet ini (bukan shift kasir lain).
    public function show(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);

        $activeShift = $outlet->shifts()
            ->where('user_id', $user->id)
            ->whereNull('closed_at')
            ->first();

        $stats = null;
        if ($activeShift) {
            $txBase = $outlet->transactions()
                ->where('status', 'completed')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $activeShift->opened_at);

            $cashIn            = (int) (clone $txBase)->where('payment_method', 'cash')->sum('total');
            $totalTransactions = (clone $txBase)->count();
            $totalRevenue      = (int) (clone $txBase)->sum('total');
            $totalExpense      = (int) $outlet->expenses()
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $activeShift->opened_at)
                ->sum('amount');

            $stats = [
                'total_transactions' => $totalTransactions,
                'total_revenue'      => $totalRevenue,
                'cash_in'            => $cashIn,
                'total_expense'      => $totalExpense,
                'expected_cash'      => (int) $activeShift->opening_cash + $cashIn - $totalExpense,
            ];
        }

        return response()->json([
            'enabled'      => (bool) $outlet->enable_opening_shift,
            'active_shift' => $activeShift ? new ShiftResource($activeShift) : null,
            'stats'        => $stats,
        ]);
    }

    public function open(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);

        if (!$outlet->enable_opening_shift) {
            return response()->json(['message' => 'Fitur shift tidak diaktifkan untuk outlet ini.'], 422);
        }

        if (!$outlet->products()->where('is_active', true)->exists()) {
            return response()->json(['message' => 'Outlet belum memiliki produk aktif.'], 422);
        }

        if ($outlet->shifts()->where('user_id', $user->id)->whereNull('closed_at')->exists()) {
            return response()->json(['message' => 'Anda masih memiliki shift yang aktif.'], 422);
        }

        $data = $request->validate([
            'opening_cash' => ['required', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $outlet->shifts()->create([
            'user_id'      => $user->id,
            'opened_by'    => $user->id,
            'opened_at'    => now(),
            'opening_cash' => $data['opening_cash'],
            'notes'        => $data['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Shift berhasil dibuka.', 'shift' => new ShiftResource($shift)], 201);
    }

    public function close(Request $request, Outlet $outlet): JsonResponse
    {
        $user = $this->authorizeOutlet($request, $outlet);

        $shift = $outlet->shifts()->where('user_id', $user->id)->whereNull('closed_at')->first();

        if (!$shift) {
            return response()->json(['message' => 'Tidak ada shift aktif untuk ditutup.'], 422);
        }

        $data = $request->validate([
            'closing_cash' => ['required', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $txBase = $outlet->transactions()
            ->where('status', 'completed')
            ->where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at);

        $cashIn            = (int) (clone $txBase)->where('payment_method', 'cash')->sum('total');
        $totalTransactions = (clone $txBase)->count();
        $totalRevenue      = (int) (clone $txBase)->sum('total');
        $totalExpense      = (int) $outlet->expenses()
            ->where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->sum('amount');

        $expectedCash = (int) $shift->opening_cash + $cashIn - $totalExpense;

        $shift->update([
            'closed_by'          => $user->id,
            'closed_at'          => now(),
            'closing_cash'       => $data['closing_cash'],
            'expected_cash'      => $expectedCash,
            'cash_in'            => $cashIn,
            'total_transactions' => $totalTransactions,
            'total_revenue'      => $totalRevenue,
            'total_expense'      => $totalExpense,
            'notes'              => $data['notes'] ?? $shift->notes,
        ]);

        return response()->json([
            'message' => 'Shift berhasil ditutup.',
            'shift'   => new ShiftResource($shift->fresh()),
            'selisih' => (int) $data['closing_cash'] - $expectedCash,
        ]);
    }
}
