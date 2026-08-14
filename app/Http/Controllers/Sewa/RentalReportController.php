<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\RentalTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RentalReportController extends Controller
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

        $rentalType = in_array($request->input('rental_type'), array_keys(RentalTransaction::TYPES), true)
            ? $request->input('rental_type')
            : null;

        $rentalsAllTypes = RentalTransaction::where('outlet_id', $outlet->id)
            ->whereBetween(DB::raw('DATE(start_at)'), [$from, $to])
            ->with(['customer', 'rentalUnit.rentalItem'])
            ->get();

        $rentals = $rentalType
            ? $rentalsAllTypes->where('rental_type', $rentalType)->values()
            : $rentalsAllTypes;

        $isLate = function (RentalTransaction $r): bool {
            if ($r->status === 'selesai' && $r->returned_at) {
                return $r->returned_at->gt($r->end_at);
            }
            return $r->isOverdue();
        };

        // Tingkat keterlambatan dipecah per Jenis Sewa — supaya sewa Per Jam yang wajar sering "mepet"
        // tidak mencampur/merusak statistik keterlambatan Per Hari/Per Bulan.
        $lateByType = collect(RentalTransaction::TYPES)->mapWithKeys(function ($label, $code) use ($rentalsAllTypes, $isLate) {
            $group = $rentalsAllTypes->where('rental_type', $code);
            $count = $group->count();
            $late  = $group->filter($isLate)->count();
            return [$code => [
                'label'    => $label,
                'count'    => $count,
                'late'     => $late,
                'late_pct' => $count > 0 ? round($late / $count * 100, 1) : 0,
            ]];
        });

        $totalRentals  = $rentals->count();
        $activeCount   = $rentals->where('status', 'aktif')->count();
        $doneCount     = $rentals->where('status', 'selesai')->count();
        $totalValue    = (int) $rentals->sum('total_amount');
        $avgValue      = $totalRentals > 0 ? round($totalValue / $totalRentals) : 0;

        $avgDurationHours = $totalRentals > 0
            ? round($rentals->avg(fn ($r) => $r->start_at->diffInHours($r->end_at)))
            : 0;

        // Keterlambatan: sudah selesai tapi dikembalikan lewat dari end_at, atau masih aktif dan sudah lewat end_at
        $lateCount = $rentals->filter($isLate)->count();
        $latePct = $totalRentals > 0 ? round($lateCount / $totalRentals * 100, 1) : 0;

        // Status pembayaran dalam periode
        $paymentStatus = collect(['Lunas', 'DP', 'Belum Bayar'])
            ->mapWithKeys(fn ($label) => [$label => $rentals->filter(fn ($r) => $r->paymentStatusLabel() === $label)->count()]);

        // Tren jumlah sewa per hari/bulan
        $daysDiff = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to));
        $groupBy  = $daysDiff > 62 ? 'month' : 'day';
        $dateFmt  = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';

        $trendRows = DB::table('rental_transactions')
            ->where('outlet_id', $outlet->id)
            ->whereBetween(DB::raw('DATE(start_at)'), [$from, $to])
            ->when($rentalType, fn ($q) => $q->where('rental_type', $rentalType))
            ->selectRaw("DATE_FORMAT(start_at,'{$dateFmt}') as period, COUNT(*) as count, COALESCE(SUM(total_amount),0) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $chartData = $trendRows->map(function ($row) use ($groupBy) {
            $label = $groupBy === 'day'
                ? \Carbon\Carbon::parse($row->period)->translatedFormat('d M')
                : \Carbon\Carbon::parse($row->period . '-01')->translatedFormat('M Y');
            return ['label' => $label, 'count' => (int) $row->count, 'total' => (int) $row->total];
        });

        // Barang paling sering disewa (top 5)
        $topItems = $rentals->groupBy(fn ($r) => $r->rentalUnit->rentalItem->name)
            ->map(fn ($g) => ['count' => $g->count(), 'total' => (int) $g->sum('total_amount')])
            ->sortByDesc('count')
            ->take(5);

        return view('sewa.reports.rentals', compact(
            'outlet', 'user', 'from', 'to', 'groupBy', 'rentalType',
            'totalRentals', 'activeCount', 'doneCount', 'totalValue', 'avgValue', 'avgDurationHours',
            'lateCount', 'latePct', 'lateByType', 'paymentStatus', 'chartData', 'topItems'
        ));
    }
}
