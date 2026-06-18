<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockViewController extends Controller
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

        if (!$user->hasPermission('stock.view')) {
            abort(403);
        }

        $outlet->load('outletType');

        // â”€â”€ Produk â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $products = $outlet->products()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $statTotal  = $products->where('is_active', true)->count();
        $statHabis  = $products->where('is_active', true)->where('stock', '<=', 0)->count();
        $statKritis = $products->where('is_active', true)
            ->filter(fn($p) => $p->stock > 0 && $p->isLowStock())
            ->count();

        $categories = $outlet->categories()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        // â”€â”€ Pergerakan Stok â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $movFrom    = $request->input('mov_from', today()->subDays(29)->format('Y-m-d'));
        $movTo      = $request->input('mov_to',   today()->format('Y-m-d'));
        $movProduct = $request->input('mov_product');
        $movType    = $request->input('mov_type');

        $movements = collect();

        // Masuk â€” Tambah Stok
        if (!$movType || $movType === 'masuk') {
            $rows = DB::table('stock_in_items as sii')
                ->join('stock_ins as si',  'sii.stock_in_id',  '=', 'si.id')
                ->join('products as p',    'sii.product_id',   '=', 'p.id')
                ->join('users as u',       'si.user_id',       '=', 'u.id')
                ->where('si.outlet_id', $outlet->id)
                ->whereDate('si.date', '>=', $movFrom)
                ->whereDate('si.date', '<=', $movTo)
                ->when($movProduct, fn($q) => $q->where('sii.product_id', $movProduct))
                ->select(
                    'si.date',
                    'si.created_at as logged_at',
                    DB::raw("'masuk' as type"),
                    'p.name as product_name',
                    'sii.product_id',
                    DB::raw('CAST(sii.qty AS SIGNED) as qty'),
                    DB::raw("'Tambah Stok' as reference"),
                    'u.name as actor',
                )
                ->get()->map(fn($r) => (array) $r);

            $movements = $movements->merge($rows);
        }

        // Keluar â€” Penjualan
        if (!$movType || $movType === 'keluar') {
            $rows = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                ->join('users as u',        't.user_id',         '=', 'u.id')
                ->where('t.outlet_id', $outlet->id)
                ->where('t.status', 'completed')
                ->whereDate('t.date', '>=', $movFrom)
                ->whereDate('t.date', '<=', $movTo)
                ->when($movProduct, fn($q) => $q->where('ti.product_id', $movProduct))
                ->select(
                    't.date',
                    't.created_at as logged_at',
                    DB::raw("'keluar' as type"),
                    'ti.product_name as product_name',
                    'ti.product_id',
                    DB::raw('-(CAST(ti.qty AS SIGNED)) as qty'),
                    DB::raw("CONCAT('Penjualan Â· ', t.transaction_number) as reference"),
                    'u.name as actor',
                )
                ->get()->map(fn($r) => (array) $r);

            $movements = $movements->merge($rows);
        }

        // Penyesuaian â€” Opening Stok
        if (!$movType || $movType === 'penyesuaian') {
            $rows = DB::table('stock_opname_items as soi')
                ->join('stock_opnames as so', 'soi.stock_opname_id', '=', 'so.id')
                ->join('products as p',       'soi.product_id',      '=', 'p.id')
                ->join('users as u',          'so.user_id',          '=', 'u.id')
                ->where('so.outlet_id', $outlet->id)
                ->whereRaw('soi.actual_qty != soi.system_qty')
                ->whereDate('so.date', '>=', $movFrom)
                ->whereDate('so.date', '<=', $movTo)
                ->when($movProduct, fn($q) => $q->where('soi.product_id', $movProduct))
                ->select(
                    'so.date',
                    'so.created_at as logged_at',
                    DB::raw("'penyesuaian' as type"),
                    'p.name as product_name',
                    'soi.product_id',
                    DB::raw('(CAST(soi.actual_qty AS SIGNED) - CAST(soi.system_qty AS SIGNED)) as qty'),
                    DB::raw("'Opening Stok' as reference"),
                    'u.name as actor',
                )
                ->get()->map(fn($r) => (array) $r);

            $movements = $movements->merge($rows);
        }

        // Keluar â€” Waste / Rusak
        if (!$movType || $movType === 'waste') {
            $reasonLabels = ['sisa' => 'Sisa / Tidak Terjual', 'rusak' => 'Rusak / Cacat', 'expired' => 'Kedaluwarsa', 'lainnya' => 'Lainnya'];

            $rows = DB::table('waste_items as wi')
                ->join('wastes as w',        'wi.waste_id',   '=', 'w.id')
                ->leftJoin('products as p',  'wi.product_id', '=', 'p.id')
                ->join('users as u',         'w.user_id',     '=', 'u.id')
                ->where('w.outlet_id', $outlet->id)
                ->whereDate('w.date', '>=', $movFrom)
                ->whereDate('w.date', '<=', $movTo)
                ->when($movProduct, fn($q) => $q->where('wi.product_id', $movProduct))
                ->select(
                    'w.date',
                    DB::raw('COALESCE(w.submitted_at, w.created_at) as logged_at'),
                    DB::raw("'waste' as type"),
                    'wi.product_name',
                    'wi.product_id',
                    DB::raw('-(CAST(wi.qty AS SIGNED)) as qty'),
                    'wi.reason',
                    'u.name as actor',
                )
                ->get()
                ->map(function ($r) use ($reasonLabels) {
                    $r = (array) $r;
                    $r['reference'] = 'Waste Â· ' . ($reasonLabels[$r['reason']] ?? $r['reason']);
                    unset($r['reason']);
                    return $r;
                });

            $movements = $movements->merge($rows);
        }

        $movements = $movements->sortByDesc('logged_at')->values();

        $activeTab = $request->input('tab', 'stok');

        return view('laundry.stock.index', compact(
            'outlet', 'user', 'products', 'categories',
            'statTotal', 'statHabis', 'statKritis',
            'movements', 'movFrom', 'movTo', 'movProduct', 'movType',
            'activeTab'
        ));
    }
}
