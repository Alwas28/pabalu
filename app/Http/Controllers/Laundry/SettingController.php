<?php

namespace App\Http\Controllers\Laundry;

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

        return view('laundry.settings', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($outlet);

        $validated = $request->validate([
            'order_mode'             => ['nullable', 'in:quick,kitchen'],
            'enable_opening_shift'   => ['boolean'],
            'enable_barcode_scanner' => ['boolean'],
        ]);

        $outlet->update([
            'order_mode'             => $validated['order_mode'] ?? 'quick',
            'enable_opening_shift'   => $request->boolean('enable_opening_shift'),
            'enable_barcode_scanner' => $request->boolean('enable_barcode_scanner'),
        ]);

        return back()->with('success', 'Pengaturan outlet berhasil disimpan.');
    }
}
