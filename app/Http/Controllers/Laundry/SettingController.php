<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\EnforcesProFeature;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    use EnforcesProFeature;

    private function authorizeOwner(Outlet $outlet): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && (int) $outlet->owner_id !== (int) $user->id) {
            abort(403, 'Hanya pemilik atau admin yang dapat mengubah pengaturan outlet.');
        }
    }

    public function edit(Outlet $outlet): View
    {
        $this->authorizeOwner($outlet);
        $outlet->load('outletType');

        return view('laundry.settings', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $validated = $request->validate([
            'enable_opening_shift'   => ['boolean'],
            'enable_barcode_scanner' => ['boolean'],
            'enable_qris_transfer'   => ['boolean'],
            'enable_qris_pay'        => ['boolean'],
            'enable_transfer'        => ['boolean'],
            'enable_card'            => ['boolean'],
            'midtrans_server_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_client_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['boolean'],
        ]);

        /** @var \App\Models\User $actingUser */
        $actingUser       = Auth::user();
        $hasPaymentCustom = $this->ownerHasProFeature($outlet, $actingUser, 'laundry_payment_custom');
        $hasMidtrans      = $this->ownerHasProFeature($outlet, $actingUser, 'laundry_midtrans');

        // QRIS Pay hanya bisa diubah oleh admin, dan hanya kalau paket owner termasuk Midtrans;
        // non-admin atau paket tanpa fitur ini preserve nilai lama (tidak bisa dinyalakan).
        $enableQrisPay = ($hasMidtrans && $actingUser->role === 'admin')
            ? $request->boolean('enable_qris_pay')
            : ($hasMidtrans ? $outlet->enable_qris_pay : false);

        if ($enableQrisPay && !$request->filled('midtrans_server_key') && !$outlet->midtrans_server_key) {
            return back()->withErrors(['midtrans_server_key' => 'Server Key Midtrans wajib diisi untuk mengaktifkan QRIS Pay.'])->withInput();
        }

        $outlet->update([
            'order_mode'             => 'quick',
            'enable_opening_shift'   => $request->boolean('enable_opening_shift'),
            'enable_barcode_scanner' => $request->boolean('enable_barcode_scanner'),
            'enable_qris_transfer'   => $hasPaymentCustom && $request->boolean('enable_qris_transfer'),
            'enable_qris_pay'        => $enableQrisPay,
            'enable_transfer'        => $hasPaymentCustom && $request->boolean('enable_transfer'),
            'enable_card'            => $hasPaymentCustom && $request->boolean('enable_card'),
            // Server/Client Key & mode produksi Midtrans HANYA bisa diubah admin sistem —
            // owner tidak pernah melihat form-nya (lihat @if($isSystemAdmin) di view), tapi
            // dijaga juga di sini kalau-kalau ada yang kirim field ini lewat request mentah.
            'midtrans_server_key'    => ($actingUser->role === 'admin' && $request->filled('midtrans_server_key')) ? ($validated['midtrans_server_key'] ?? null) : $outlet->midtrans_server_key,
            'midtrans_client_key'    => ($actingUser->role === 'admin' && $request->filled('midtrans_client_key')) ? ($validated['midtrans_client_key'] ?? null) : $outlet->midtrans_client_key,
            'midtrans_is_production' => $actingUser->role === 'admin' ? $request->boolean('midtrans_is_production') : $outlet->midtrans_is_production,
        ]);

        return back()->with('success', 'Pengaturan outlet berhasil disimpan.');
    }
}
