<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OwnerSetting;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPaymentSettingController extends Controller
{
    public function index(): View
    {
        abort_if(Setting::get('midtrans_enabled') !== '1', 403,
            'Fitur Payment Gateway belum diaktifkan oleh administrator.');

        $user       = auth()->user();
        $ownerId    = $user->ownerAccount()?->id ?? $user->id;
        $settings   = OwnerSetting::getForOwner($ownerId);
        $hasKeys    = !empty($settings['midtrans_server_key']);

        $adminWaRaw = \App\Models\User::role('admin')->with('profile')->first()?->profile?->no_hp;
        $adminWa    = $adminWaRaw
            ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', ltrim($adminWaRaw, '0'))
            : null;
        $adminWa    = $adminWa ? str_replace('wa.me/0', 'wa.me/62', $adminWa) : null;

        return view('owner.payment-settings', compact('settings', 'hasKeys', 'adminWa'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Setting::get('midtrans_enabled') !== '1', 403);

        $user    = auth()->user();
        $ownerId = $user->ownerAccount()?->id ?? $user->id;

        // Owner hanya boleh toggle aktif/nonaktif — key dikonfigurasi oleh admin
        $enabled = $request->boolean('midtrans_enabled') ? '1' : '0';
        OwnerSetting::set('midtrans_enabled', $enabled, $ownerId);

        ActivityLog::record('update_payment_setting',
            'Payment gateway ' . ($enabled === '1' ? 'diaktifkan' : 'dinonaktifkan') . '.');

        return back()->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
}
