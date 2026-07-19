<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        return $user;
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('transaction.view')) {
            abort(403);
        }

        $outlet->load('outletType');

        $isKasir = $user->role === 'kasir';

        // Monthly stats (completed only) — kasir sees only their own
        $monthQuery = $outlet->transactions()
            ->where('status', 'completed')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->when($isKasir, fn ($q) => $q->where('user_id', $user->id));

        $statCount   = (clone $monthQuery)->count();
        $statRevenue = (clone $monthQuery)->sum('total');
        $statAvg     = $statCount > 0 ? intdiv($statRevenue, $statCount) : 0;

        // Breakdown by payment method this month
        $byMethod = (clone $monthQuery)
            ->select('payment_method', DB::raw('SUM(total) as total_sum'), DB::raw('COUNT(*) as trx_count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // Paginated list — kasir sees only their own transactions
        $transactions = $outlet->transactions()
            ->with('user')
            ->withCount('items')
            ->when($isKasir, fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('method'),    fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->status))
            ->when(!$isKasir && $request->filled('cashier'), fn ($q) => $q->where('user_id', $request->cashier))
            ->when($request->filled('search'),    fn ($q) => $q->where('transaction_number', 'like', '%'.$request->search.'%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        // Cashier filter dropdown — only visible to non-kasir roles
        $cashiers = $isKasir
            ? collect()
            : $outlet->transactions()
                ->select('user_id')
                ->distinct()
                ->with('user:id,name')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->sortBy('name');

        return view('salon.transactions.index', compact(
            'outlet', 'user', 'transactions',
            'statCount', 'statRevenue', 'statAvg',
            'byMethod', 'cashiers', 'isKasir'
        ));
    }

    public function show(Outlet $outlet, Transaction $transaction): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('transaction.view')) {
            abort(403);
        }

        if ($transaction->outlet_id != $outlet->id) {
            abort(404);
        }

        if ($user->role === 'kasir' && $transaction->user_id !== $user->id) {
            abort(403);
        }

        $outlet->load('outletType');
        $transaction->load(['items', 'user']);

        $canVoid = $user->hasPermission('transaction.void')
            && $transaction->status === 'completed';

        return view('salon.transactions.show', compact('outlet', 'user', 'transaction', 'canVoid'));
    }

    public function receipt(Outlet $outlet, Transaction $transaction): \Illuminate\View\View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('transaction.view')) {
            abort(403);
        }

        if ($transaction->outlet_id != $outlet->id) {
            abort(404);
        }

        if ($user->role === 'kasir' && $transaction->user_id !== $user->id) {
            abort(403);
        }

        $outlet->load('outletType');
        $transaction->load(['items', 'user']);

        return view('salon.transactions.receipt', compact('outlet', 'transaction'));
    }

    public function void(Request $request, Outlet $outlet, Transaction $transaction): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('transaction.void')) {
            abort(403);
        }

        if ($transaction->outlet_id != $outlet->id) {
            abort(404);
        }

        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Transaksi ini sudah divoid sebelumnya.');
        }

        DB::transaction(function () use ($outlet, $transaction) {
            // Kembalikan stok
            foreach ($transaction->items as $item) {
                if ($item->product_id) {
                    $product = $outlet->products()->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->qty);
                    }
                }
            }
            $transaction->update(['status' => 'voided']);
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} berhasil divoid. Stok telah dikembalikan.");
    }
}
