<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user             = auth()->user();
        $outlets          = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = $user->assignedOutletId();
        $tanggal          = today()->toDateString();
        $kasirId          = $user->isKasir() ? $user->id : null;

        // outletId: null = semua outlet, int = outlet spesifik
        $requestedOutlet = $request->get('outlet_id');
        if ($assignedOutletId) {
            $outletId = $assignedOutletId;
        } elseif ($requestedOutlet !== null && $requestedOutlet !== '') {
            $outletId = (int) $requestedOutlet;
        } else {
            // default: semua outlet (null), kecuali hanya 1 outlet maka langsung pilih
            $outletId = $outlets->count() === 1 ? $outlets->first()->id : null;
        }

        // IDs outlet yang dipakai untuk query — bisa 1 atau semua
        $outletIds = $outletId
            ? collect([$outletId])
            : $outlets->pluck('id');

        // ── Stat hari ini ────────────────────────────────
        $threshold      = (int) Setting::get('low_stock_threshold', 5);
        $omzetHariIni   = 0;
        $trxHariIni     = 0;
        $expenseHariIni = 0;
        $itemTerjual    = 0;
        $stokKritis     = collect();
        $recentTrx      = collect();
        $chartMinggu    = [];
        $chartBulan     = [];

        if ($outletIds->isNotEmpty()) {
            $trxQuery = Transaction::whereIn('outlet_id', $outletIds)
                ->where('tanggal', $tanggal)
                ->where('status', 'paid')
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId));

            $omzetHariIni = (clone $trxQuery)->sum('total');
            $trxHariIni   = (clone $trxQuery)->count();

            $itemTerjual = TransactionItem::whereHas('transaction', fn($q) =>
                $q->whereIn('outlet_id', $outletIds)
                  ->where('tanggal', $tanggal)
                  ->where('status', 'paid')
                  ->when($kasirId, fn($q2) => $q2->where('kasir_id', $kasirId))
            )->sum('qty');

            $expenseHariIni = $kasirId ? 0 : Expense::whereIn('outlet_id', $outletIds)
                ->where('tanggal', $tanggal)
                ->sum('jumlah');

            // ── Stok kritis ─
            $stokKritis = Product::with('outlet')
                ->whereIn('outlet_id', $outletIds)
                ->where('is_active', true)
                ->get()
                ->map(fn($p) => [
                    'nama'   => $p->nama,
                    'outlet' => $p->outlet?->nama,
                    'stok'   => StockMovement::currentStock($p->outlet_id, $p->id, $tanggal),
                    'satuan' => $p->satuan,
                ])
                ->filter(fn($p) => $p['stok'] <= $threshold)
                ->sortBy('stok')
                ->values();

            // ── Transaksi terbaru ──────────────────────
            $recentTrx = Transaction::with(['kasir', 'items', 'outlet'])
                ->whereIn('outlet_id', $outletIds)
                ->where('tanggal', $tanggal)
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
                ->latest()
                ->limit(8)
                ->get();

            // ── Grafik ───────────────────────────────
            $chartMinggu = $this->buildChart($outletIds->toArray(), 7,  $kasirId);
            $chartBulan  = $this->buildChart($outletIds->toArray(), 30, $kasirId);
        }

        $labaHariIni = $omzetHariIni - $expenseHariIni;

        $omzetKemarin = $outletIds->isNotEmpty()
            ? Transaction::whereIn('outlet_id', $outletIds)
                ->where('tanggal', now()->subDay()->toDateString())
                ->where('status', 'paid')
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
                ->sum('total')
            : 0;

        $noOutlets = $outlets->isEmpty();

        return view('dashboard', compact(
            'outlets', 'outletId', 'assignedOutletId', 'tanggal',
            'omzetHariIni', 'trxHariIni', 'itemTerjual', 'expenseHariIni',
            'labaHariIni', 'omzetKemarin',
            'stokKritis', 'recentTrx',
            'chartMinggu', 'chartBulan',
            'threshold', 'noOutlets'
        ));
    }

    private function buildChart(array $outletIds, int $days, ?int $kasirId = null): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date  = Carbon::today()->subDays($i);
            $omzet = Transaction::whereIn('outlet_id', $outletIds)
                ->where('tanggal', $date->toDateString())
                ->where('status', 'paid')
                ->when($kasirId, fn($q) => $q->where('kasir_id', $kasirId))
                ->sum('total');

            $result[] = [
                'label' => $days <= 7
                    ? $date->locale('id')->isoFormat('ddd')
                    : $date->format('d/m'),
                'value' => (float) $omzet,
                'date'  => $date->toDateString(),
            ];
        }
        return $result;
    }
}
