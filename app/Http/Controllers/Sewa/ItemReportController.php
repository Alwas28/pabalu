<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\RentalItem;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ItemReportController extends Controller
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

        if (!$user->hasPermission('report.sales')) {
            abort(403);
        }

        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', today()->format('Y-m-d'));

        $periodStart = Carbon::parse($from)->startOfDay();
        $periodEnd   = Carbon::parse($to)->endOfDay();
        // Hitung jumlah hari dari tanggal murni (bukan periodEnd yang sudah 23:59:59) agar tidak meleset
        // karena diffInDays() Carbon mengembalikan pecahan hari, bukan dibulatkan ke hari kalender.
        $periodDays = max(1, (int) Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->startOfDay()) + 1);

        // Sewa yang beririsan dengan periode (mulai sebelum akhir periode & selesai setelah awal periode)
        $rentals = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('start_at', '<=', $periodEnd)
            ->where('end_at', '>=', $periodStart)
            ->with('rentalUnit.rentalItem')
            ->get();

        // Top barang disewa (jumlah transaksi & pendapatan disepakati/total_amount)
        $topItems = $rentals->groupBy(fn ($r) => $r->rentalUnit->rentalItem->name ?? '—')
            ->map(fn ($g) => [
                'count' => $g->count(),
                'total' => (int) $g->sum('total_amount'),
            ])
            ->sortByDesc('count');

        // Utilisasi per unit: total hari disewa (dipotong ke rentang periode) / total hari periode
        $units = RentalUnit::whereHas('rentalItem', fn ($q) => $q->where('outlet_id', $outlet->id))
            ->with('rentalItem')
            ->orderBy('code')
            ->get();

        $rentalsByUnit = $rentals->groupBy('rental_unit_id');

        $utilization = $units->map(function ($unit) use ($rentalsByUnit, $periodStart, $periodEnd, $periodDays) {
            $unitRentals = $rentalsByUnit->get($unit->id, collect());

            $rentedDays = 0;
            foreach ($unitRentals as $r) {
                $overlapStart = $r->start_at->greaterThan($periodStart) ? $r->start_at : $periodStart;
                $overlapEnd   = $r->end_at->lessThan($periodEnd) ? $r->end_at : $periodEnd;
                if ($overlapEnd->greaterThan($overlapStart)) {
                    $rentedDays += $overlapStart->diffInHours($overlapEnd) / 24;
                }
            }

            $pct = $periodDays > 0 ? min(100, round(($rentedDays / $periodDays) * 100, 1)) : 0;

            return [
                'unit'        => $unit,
                'trx_count'   => $unitRentals->count(),
                'utilization' => $pct,
            ];
        })->sortByDesc('utilization')->values();

        $avgUtilization = $utilization->isNotEmpty() ? round($utilization->avg('utilization'), 1) : 0;

        // Status unit saat ini
        $statusBreakdown = collect(RentalUnit::STATUSES)->keys()
            ->mapWithKeys(fn ($status) => [$status => $units->where('status', $status)->count()]);

        $totalItems = RentalItem::where('outlet_id', $outlet->id)->count();
        $totalUnits = $units->count();

        return view('sewa.reports.items', compact(
            'outlet', 'user', 'from', 'to',
            'topItems', 'utilization', 'avgUtilization', 'statusBreakdown', 'totalItems', 'totalUnits'
        ));
    }
}
