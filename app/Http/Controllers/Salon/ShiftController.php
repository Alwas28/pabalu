<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftController extends Controller
{
    private function authorizeAccess(Outlet $outlet): User
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
        $user = $this->authorizeAccess($outlet);

        if (!$outlet->enable_opening_shift) {
            abort(404, 'Fitur Opening Shift belum diaktifkan untuk outlet ini.');
        }

        $outlet->load('outletType');

        // Shift milik user yang sedang login
        $activeShift = $outlet->shifts()
            ->where('user_id', $user->id)
            ->whereNull('closed_at')
            ->first();

        // Semua shift aktif di outlet — ditampilkan untuk admin/owner/admin_outlet
        $allActiveShifts = null;
        if (in_array($user->role, ['admin', 'owner', 'admin_outlet'])) {
            $allActiveShifts = $outlet->shifts()
                ->with('openedBy')
                ->whereNull('closed_at')
                ->get();
        }

        $stats = null;
        if ($activeShift) {
            $txBase = $outlet->transactions()
                ->where('status', 'completed')
                ->where('user_id', $activeShift->user_id)
                ->where('created_at', '>=', $activeShift->opened_at);

            $cashIn            = (int) (clone $txBase)->where('payment_method', 'cash')->sum('total');
            $totalTransactions = (clone $txBase)->count();
            $totalRevenue      = (int) (clone $txBase)->sum('total');

            $totalExpense = (int) $outlet->expenses()
                ->where('user_id', $activeShift->user_id)
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

        $hasProducts = $outlet->products()->where('is_active', true)->exists();

        $shifts = $outlet->shifts()
            ->with(['openedBy', 'closedBy'])
            ->orderByDesc('opened_at')
            ->paginate(15);

        return view('salon.shifts.index', compact(
            'outlet', 'activeShift', 'allActiveShifts', 'stats', 'shifts', 'hasProducts'
        ));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeAccess($outlet);

        if (!$outlet->enable_opening_shift) {
            abort(404);
        }

        if (!$outlet->products()->where('is_active', true)->exists()) {
            return back()->with('error', 'Tidak dapat membuka shift — outlet belum memiliki produk aktif. Tambahkan produk terlebih dahulu.');
        }

        if ($outlet->shifts()->where('user_id', $user->id)->whereNull('closed_at')->exists()) {
            return back()->with('error', 'Anda masih memiliki shift yang aktif. Tutup shift terlebih dahulu.');
        }

        $validated = $request->validate([
            'opening_cash' => ['required', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $outlet->shifts()->create([
            'user_id'      => $user->id,
            'opened_by'    => $user->id,
            'opened_at'    => now(),
            'opening_cash' => $validated['opening_cash'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Shift berhasil dibuka. Selamat bekerja!');
    }

    public function close(Request $request, Outlet $outlet, Shift $shift): RedirectResponse
    {
        $user = $this->authorizeAccess($outlet);

        if ($shift->outlet_id !== $outlet->id || !$shift->isActive()) {
            abort(404);
        }

        if ($shift->user_id !== $user->id && !$user->hasPermission('shift.manage')) {
            abort(403);
        }

        $validated = $request->validate([
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

        $totalExpense = (int) $outlet->expenses()
            ->where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->sum('amount');

        $expectedCash = (int) $shift->opening_cash + $cashIn - $totalExpense;

        $shift->update([
            'closed_by'          => $user->id,
            'closed_at'          => now(),
            'closing_cash'       => $validated['closing_cash'],
            'expected_cash'      => $expectedCash,
            'cash_in'            => $cashIn,
            'total_transactions' => $totalTransactions,
            'total_revenue'      => $totalRevenue,
            'total_expense'      => $totalExpense,
            'notes'              => $validated['notes'] ?? $shift->notes,
        ]);

        $selisih = (int) $validated['closing_cash'] - $expectedCash;
        $msg = $selisih === 0
            ? 'Shift ditutup. Kas sesuai — tidak ada selisih.'
            : ($selisih > 0
                ? 'Shift ditutup. Kelebihan kas Rp ' . number_format(abs($selisih), 0, ',', '.')
                : 'Shift ditutup. Kekurangan kas Rp ' . number_format(abs($selisih), 0, ',', '.'));

        return back()->with('success', $msg);
    }

    public function update(Request $request, Outlet $outlet, Shift $shift): RedirectResponse
    {
        $user = $this->authorizeAccess($outlet);

        if (!$user->hasPermission('shift.manage')) {
            abort(403);
        }

        if ($shift->outlet_id !== $outlet->id) {
            abort(404);
        }

        $rules = ['notes' => ['nullable', 'string', 'max:500']];

        if ($shift->isActive()) {
            $rules['opening_cash'] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);

        $data = ['notes' => $validated['notes'] ?? null];
        if ($shift->isActive() && isset($validated['opening_cash'])) {
            $data['opening_cash'] = $validated['opening_cash'];
        }

        $shift->update($data);

        return back()->with('success', 'Data shift berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, Shift $shift): RedirectResponse
    {
        $user = $this->authorizeAccess($outlet);

        if (!$user->hasPermission('shift.manage')) {
            abort(403);
        }

        if ($shift->outlet_id !== $outlet->id) {
            abort(404);
        }

        $shift->delete();

        return back()->with('success', 'Data shift berhasil dihapus.');
    }
}
