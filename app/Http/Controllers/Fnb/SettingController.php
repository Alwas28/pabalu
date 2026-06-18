<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    private function authorizeOwner(Outlet $outlet): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $outlet->owner_id !== $user->id) {
            abort(403, 'Hanya pemilik atau admin yang dapat mengubah pengaturan outlet.');
        }
    }

    public function edit(Outlet $outlet): View
    {
        $this->authorizeOwner($outlet);
        $outlet->load('outletType');

        return view('fnb.settings', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $validated = $request->validate([
            'order_mode'              => ['required', 'in:quick,kitchen'],
            'enable_opening_shift'    => ['boolean'],
            'enable_barcode_scanner'  => ['boolean'],
            'enable_self_order'       => ['boolean'],
            'enable_qris_transfer'    => ['boolean'],
            'enable_qris_pay'         => ['boolean'],
            'enable_transfer'         => ['boolean'],
            'enable_card'             => ['boolean'],
            'midtrans_server_key'     => ['nullable', 'string', 'max:255'],
            'midtrans_client_key'     => ['nullable', 'string', 'max:255'],
            'midtrans_is_production'  => ['boolean'],
        ]);

        if ($request->boolean('enable_qris_pay') && !$request->filled('midtrans_server_key')) {
            return back()->withErrors(['midtrans_server_key' => 'Server Key Midtrans wajib diisi untuk mengaktifkan QRIS Pay.'])->withInput();
        }

        $outlet->update([
            'order_mode'              => $validated['order_mode'],
            'enable_opening_shift'    => $request->boolean('enable_opening_shift'),
            'enable_barcode_scanner'  => $request->boolean('enable_barcode_scanner'),
            'enable_self_order'       => $request->boolean('enable_self_order'),
            'enable_qris_transfer'    => $request->boolean('enable_qris_transfer'),
            'enable_qris_pay'         => $request->boolean('enable_qris_pay'),
            'enable_transfer'         => $request->boolean('enable_transfer'),
            'enable_card'             => $request->boolean('enable_card'),
            'midtrans_server_key'     => $validated['midtrans_server_key'] ?? null,
            'midtrans_client_key'     => $validated['midtrans_client_key'] ?? null,
            'midtrans_is_production'  => $request->boolean('midtrans_is_production'),
        ]);

        return back()->with('success', 'Pengaturan outlet berhasil disimpan.');
    }
}
