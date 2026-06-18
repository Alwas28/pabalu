<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ResolvesBusinessDate;
use App\Models\DailyClosing;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    use ResolvesBusinessDate;

    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        return $user;
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.closing')) {
            abort(403);
        }

        $outlet->load(['outletType', 'dailyClosings']);
        $businessDate = $this->getBusinessDate($outlet);

        // Closing untuk hari bisnis aktif
        $todayClosing = $outlet->dailyClosings()
            ->with('user')
            ->where('date', $businessDate)
            ->first();

        // Transaksi hari bisnis aktif (pakai kolom date, bukan created_at)
        $transactions = $outlet->transactions()
            ->where('date', $businessDate)
            ->where('status', 'completed')
            ->with('user')
            ->get();

        $totalTransactions = $transactions->count();
        $totalRevenue      = $transactions->sum('total');
        $totalDiscount     = $transactions->sum('discount_amount');

        // Breakdown per metode bayar
        $byMethod = collect([
            'cash'     => ['label' => 'Tunai',    'icon' => 'money-bill-wave',  'color' => '#34d399'],
            'qris'     => ['label' => 'QRIS',     'icon' => 'qrcode',           'color' => '#818cf8'],
            'transfer' => ['label' => 'Transfer', 'icon' => 'building-columns', 'color' => '#60a5fa'],
            'card'     => ['label' => 'Kartu',    'icon' => 'credit-card',      'color' => '#f59e0b'],
        ])->map(function ($meta, $key) use ($transactions) {
            $methods = $key === 'qris' ? ['qris', 'qris_transfer', 'qris_pay'] : [$key];
            $group   = $transactions->whereIn('payment_method', $methods);
            return array_merge($meta, [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ]);
        });

        // Pengeluaran hari bisnis aktif
        $expenses     = $outlet->expenses()->where('date', $businessDate)->orderBy('created_at')->get();
        $totalExpense = (int) $expenses->sum('amount');

        $cashIn    = $transactions->where('payment_method', 'cash')->sum('total');
        $kasBersih = $cashIn - $totalExpense;

        // Opening stok hari bisnis aktif
        $openingOpname = $outlet->stockOpnames()
            ->with(['items.product.category'])
            ->where('date', $businessDate)
            ->where('type', 'opening')
            ->first();

        // Penjualan per produk hari bisnis aktif
        $salesByProduct = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->where('t.outlet_id', $outlet->id)
            ->where('t.status', 'completed')
            ->where('t.date', $businessDate)
            ->select('ti.product_id', DB::raw('SUM(ti.qty) as sold_qty'), DB::raw('SUM(ti.subtotal) as revenue'))
            ->groupBy('ti.product_id')
            ->get()
            ->keyBy('product_id');

        // Waste hari bisnis aktif
        $wastes = $outlet->wastes()
            ->with(['items.product', 'user'])
            ->where('date', $businessDate)
            ->orderByDesc('submitted_at')
            ->get();

        $wasteByProduct = DB::table('waste_items as wi')
            ->join('wastes as w', 'wi.waste_id', '=', 'w.id')
            ->where('w.outlet_id', $outlet->id)
            ->where('w.date', $businessDate)
            ->select('wi.product_id', DB::raw('SUM(wi.qty) as waste_qty'))
            ->groupBy('wi.product_id')
            ->get()
            ->keyBy('product_id');

        $totalWasteQty = (int) $wasteByProduct->sum('waste_qty');

        // Produk aktif
        $products = $outlet->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $cashiers = $transactions->pluck('user')->filter()->unique('id')->values();

        // Riwayat closing
        $history = $outlet->dailyClosings()
            ->with('user')
            ->orderByDesc('date')
            ->paginate(10);

        // Untuk tampilan heading (bisa berbeda dari today() jika melewati tengah malam)
        $today = \Carbon\Carbon::parse($businessDate);

        return view('fnb.closing.index', compact(
            'outlet', 'user', 'today', 'todayClosing',
            'transactions', 'totalTransactions', 'totalRevenue', 'totalDiscount',
            'byMethod', 'expenses', 'totalExpense', 'cashIn', 'kasBersih',
            'openingOpname', 'products', 'salesByProduct',
            'wastes', 'wasteByProduct', 'totalWasteQty',
            'cashiers', 'history'
        ));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.closing')) {
            abort(403);
        }

        $outlet->load(['outletType', 'dailyClosings']);

        $businessDate = $this->getBusinessDate($outlet);

        if ($outlet->dailyClosings()->where('date', $businessDate)->exists()) {
            return back()->with('error', 'Closing hari bisnis ini sudah dilakukan.');
        }

        $transactionCount = $outlet->transactions()
            ->where('date', $businessDate)
            ->where('status', 'completed')
            ->count();

        if ($transactionCount === 0) {
            return back()->with('error', 'Closing tidak dapat dilakukan karena belum ada transaksi hari ini.');
        }

        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $transactions = $outlet->transactions()
            ->where('date', $businessDate)
            ->where('status', 'completed')
            ->get();

        $totalExpense = (int) $outlet->expenses()->where('date', $businessDate)->sum('amount');

        $stocksSnapshot = $outlet->products()
            ->where('is_active', true)
            ->pluck('stock', 'id')
            ->toArray();

        DailyClosing::create([
            'outlet_id'          => $outlet->id,
            'user_id'            => $user->id,
            'date'               => $businessDate,
            'total_transactions' => $transactions->count(),
            'total_revenue'      => $transactions->sum('total'),
            'total_discount'     => $transactions->sum('discount_amount'),
            'total_expense'      => $totalExpense,
            'cash_in'            => $transactions->where('payment_method', 'cash')->sum('total'),
            'qris_in'            => $transactions->whereIn('payment_method', ['qris', 'qris_transfer', 'qris_pay'])->sum('total'),
            'transfer_in'        => $transactions->where('payment_method', 'transfer')->sum('total'),
            'card_in'            => $transactions->where('payment_method', 'card')->sum('total'),
            'notes'              => $request->notes,
            'stocks_snapshot'    => json_encode($stocksSnapshot),
            'closed_at'          => now(),
        ]);

        $outlet->products()->where('is_active', true)->update(['stock' => 0]);

        return back()->with('success', 'Closing harian berhasil disimpan. Stok produk direset ke 0.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('stock.closing')) {
            abort(403);
        }

        $outlet->load(['outletType', 'dailyClosings']);

        $businessDate = $this->getBusinessDate($outlet);

        // Cari closing untuk business date ini (bisa jadi sudah closed)
        $closing = $outlet->dailyClosings()
            ->where('date', $businessDate)
            ->first();

        // Jika business date sudah bergeser (sudah closed), cari closing terbaru
        if (!$closing) {
            $closing = $outlet->dailyClosings()->orderByDesc('date')->firstOrFail();
        }

        if ($closing->stocks_snapshot) {
            $snapshot = json_decode($closing->stocks_snapshot, true);
            foreach ($snapshot as $productId => $stock) {
                $outlet->products()->where('id', $productId)->update(['stock' => $stock]);
            }
        }

        $closing->delete();

        return back()->with('success', 'Closing dibatalkan. Stok produk dikembalikan ke kondisi sebelum closing.');
    }
}
