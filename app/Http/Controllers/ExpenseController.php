<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    // ── Daftar + Form Tambah ─────────────────────────────
    public function index(Request $request): View
    {
        $outlets          = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $assignedOutletId = auth()->user()->assignedOutletId();
        $outletId         = $assignedOutletId ?? $request->get('outlet_id', $outlets->first()?->id);
        $tanggal          = $request->get('tanggal', today()->toDateString());

        $query = Expense::with(['outlet', 'user'])
            ->where('tanggal', $tanggal);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $expenses   = $query->latest()->get();
        $totalJumlah = $expenses->sum('jumlah');

        $kategoriList = Expense::KATEGORI;

        return view('expenses.index', compact(
            'outlets', 'outletId', 'assignedOutletId', 'tanggal',
            'expenses', 'totalJumlah', 'kategoriList'
        ));
    }

    // ── Simpan ───────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        if ($assignedOutletId = auth()->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $assignedOutletId]);
        }

        $validated = $request->validate([
            'outlet_id'  => ['required', 'exists:outlets,id'],
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'jumlah'     => ['required', 'numeric', 'min:1'],
        ]);

        $expense = Expense::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        ActivityLog::record('create_expense',
            "Pengeluaran \"{$expense->kategori}\" Rp " . number_format($expense->jumlah, 0, ',', '.') . " disimpan.",
            $expense
        );

        return redirect()
            ->route('expenses.index', ['outlet_id' => $validated['outlet_id'], 'tanggal' => $validated['tanggal']])
            ->with('success', 'Pengeluaran berhasil disimpan.');
    }

    // ── Update ───────────────────────────────────────────
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'jumlah'     => ['required', 'numeric', 'min:1'],
        ]);

        $expense->update($validated);

        ActivityLog::record('update_expense',
            "Pengeluaran \"{$expense->kategori}\" Rp " . number_format($expense->jumlah, 0, ',', '.') . " diperbarui.",
            $expense
        );

        return redirect()
            ->route('expenses.index', ['outlet_id' => $expense->outlet_id, 'tanggal' => $expense->tanggal->toDateString()])
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    // ── Hapus ────────────────────────────────────────────
    public function destroy(Expense $expense): RedirectResponse
    {
        $outletId = $expense->outlet_id;
        $tanggal  = $expense->tanggal->toDateString();

        ActivityLog::record('delete_expense',
            "Pengeluaran \"{$expense->kategori}\" Rp " . number_format($expense->jumlah, 0, ',', '.') . " dihapus."
        );

        $expense->delete();

        return redirect()
            ->route('expenses.index', ['outlet_id' => $outletId, 'tanggal' => $tanggal])
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
