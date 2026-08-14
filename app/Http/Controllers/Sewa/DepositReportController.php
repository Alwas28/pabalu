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

class DepositReportController extends Controller
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

        // Deposit yang dikumpulkan dari sewa yang MULAI dalam periode
        $collected = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('deposit_amount', '>', 0)
            ->whereBetween(DB::raw('DATE(start_at)'), [$from, $to])
            ->get();
        $totalCollected = (int) $collected->sum('deposit_amount');

        // Kondisi SAAT INI (independen dari periode) untuk kartu "sedang ditahan" & "menunggu refund"
        $allWithDeposit = RentalTransaction::where('outlet_id', $outlet->id)
            ->where('deposit_amount', '>', 0)
            ->with('payments')
            ->get();

        $totalHeld    = (int) $allWithDeposit->where('status', 'aktif')->sum(fn ($r) => $r->depositAvailable());
        $totalPending = (int) $allWithDeposit->filter(fn ($r) => $r->needsRefund())->sum(fn ($r) => $r->depositBalance());

        // Refund yang benar-benar diproses dalam periode
        $refunded = RentalTransaction::where('outlet_id', $outlet->id)
            ->whereNotNull('refunded_at')
            ->whereBetween(DB::raw('DATE(refunded_at)'), [$from, $to])
            ->get();
        $totalRefundedInPeriod = (int) $refunded->sum('refund_amount');

        // Tren: deposit dikumpulkan (by start_at) vs direfund (by refunded_at) per hari/bulan
        $daysDiff = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to));
        $groupBy  = $daysDiff > 62 ? 'month' : 'day';
        $dateFmt  = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';

        $collectedRows = DB::table('rental_transactions')
            ->where('outlet_id', $outlet->id)
            ->where('deposit_amount', '>', 0)
            ->whereBetween(DB::raw('DATE(start_at)'), [$from, $to])
            ->selectRaw("DATE_FORMAT(start_at,'{$dateFmt}') as period, COALESCE(SUM(deposit_amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

        $refundedRows = DB::table('rental_transactions')
            ->where('outlet_id', $outlet->id)
            ->whereNotNull('refunded_at')
            ->whereBetween(DB::raw('DATE(refunded_at)'), [$from, $to])
            ->selectRaw("DATE_FORMAT(refunded_at,'{$dateFmt}') as period, COALESCE(SUM(refund_amount),0) as total")
            ->groupBy('period')
            ->get()->keyBy('period');

        $allPeriods = $collectedRows->keys()->merge($refundedRows->keys())->unique()->sort()->values();

        $chartData = $allPeriods->map(function ($period) use ($collectedRows, $refundedRows, $groupBy) {
            $label = $groupBy === 'day'
                ? \Carbon\Carbon::parse($period)->translatedFormat('d M')
                : \Carbon\Carbon::parse($period . '-01')->translatedFormat('M Y');
            return [
                'label'     => $label,
                'collected' => (int) ($collectedRows[$period]?->total ?? 0),
                'refunded'  => (int) ($refundedRows[$period]?->total ?? 0),
            ];
        });

        $heldCount    = $allWithDeposit->where('status', 'aktif')->count();
        $pendingCount = $allWithDeposit->filter(fn ($r) => $r->needsRefund())->count();
        $refundedCount = $refunded->count();

        return view('sewa.reports.deposits', compact(
            'outlet', 'user', 'from', 'to', 'groupBy',
            'totalCollected', 'totalHeld', 'totalPending', 'totalRefundedInPeriod',
            'heldCount', 'pendingCount', 'refundedCount', 'chartData'
        ));
    }
}
