<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseApiController extends Controller
{
    private function checkOutletAccess(Request $request, int $outletId): ?JsonResponse
    {
        $ok = $request->user()->accessibleOutlets()->where('id', $outletId)->exists();
        return $ok ? null : response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
    }

    // ── Daftar Pengeluaran ───────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'tanggal'   => ['nullable', 'date'],
            'kategori'  => ['nullable', 'string'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $tanggal = $request->tanggal ?? today()->toDateString();

        $expenses = Expense::with('user:id,name')
            ->where('outlet_id', $request->outlet_id)
            ->where('tanggal', $tanggal)
            ->when($request->kategori, fn($q) => $q->where('kategori', $request->kategori))
            ->latest()
            ->get()
            ->map(fn($e) => [
                'id'         => $e->id,
                'tanggal'    => $e->tanggal->toDateString(),
                'kategori'   => $e->kategori,
                'keterangan' => $e->keterangan,
                'jumlah'     => (int) $e->jumlah,
                'user'       => $e->user?->name,
            ]);

        return response()->json([
            'tanggal'    => $tanggal,
            'total'      => $expenses->sum('jumlah'),
            'expenses'   => $expenses,
            'kategori_list' => Expense::KATEGORI,
        ]);
    }

    // ── Simpan Pengeluaran ───────────────────────────────
    public function store(Request $request): JsonResponse
    {
        if ($id = $request->user()->assignedOutletId()) {
            $request->merge(['outlet_id' => $id]);
        }

        $request->validate([
            'outlet_id'  => ['required', 'exists:outlets,id'],
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'jumlah'     => ['required', 'numeric', 'min:1'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $expense = Expense::create([
            'outlet_id'  => $request->outlet_id,
            'tanggal'    => $request->tanggal,
            'kategori'   => $request->kategori,
            'keterangan' => $request->keterangan,
            'jumlah'     => $request->jumlah,
            'user_id'    => $request->user()->id,
        ]);

        ActivityLog::record('create_expense',
            "Pengeluaran \"{$expense->kategori}\" Rp " . number_format($expense->jumlah, 0, ',', '.') . " disimpan via API.",
            $expense
        );

        return response()->json([
            'message' => 'Pengeluaran berhasil disimpan.',
            'id'      => $expense->id,
        ], 201);
    }

    // ── Update Pengeluaran ───────────────────────────────
    public function update(Request $request, Expense $expense): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $expense->outlet_id)) return $err;

        $request->validate([
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'jumlah'     => ['required', 'numeric', 'min:1'],
        ]);

        $expense->update($request->only(['tanggal', 'kategori', 'keterangan', 'jumlah']));

        ActivityLog::record('update_expense',
            "Pengeluaran \"{$expense->kategori}\" diperbarui via API.",
            $expense
        );

        return response()->json(['message' => 'Pengeluaran berhasil diperbarui.']);
    }

    // ── Hapus Pengeluaran ────────────────────────────────
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $expense->outlet_id)) return $err;

        ActivityLog::record('delete_expense',
            "Pengeluaran \"{$expense->kategori}\" Rp " . number_format($expense->jumlah, 0, ',', '.') . " dihapus via API."
        );

        $expense->delete();

        return response()->json(['message' => 'Pengeluaran berhasil dihapus.']);
    }
}
