<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OwnerSetting;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPaymentMethodController extends Controller
{
    public const METHODS = [
        'tunai'    => ['label' => 'Tunai',         'icon' => 'fa-money-bill-wave', 'color' => '#34d399', 'required' => true],
        'qris'     => ['label' => 'QRIS',           'icon' => 'fa-qrcode',          'color' => '#818cf8', 'required' => false],
        'transfer' => ['label' => 'Transfer Bank',  'icon' => 'fa-building-columns', 'color' => '#60a5fa', 'required' => false],
        'gateway'  => ['label' => 'Payment Gateway','icon' => 'fa-credit-card',     'color' => '#f59e0b', 'required' => false, 'gateway' => true],
    ];

    public function index(): View
    {
        $user    = auth()->user();
        $ownerId = $user->ownerAccount()?->id ?? $user->id;
        $s       = OwnerSetting::getForOwner($ownerId);

        $methods = self::METHODS;
        $enabled = [
            'tunai'    => ($s['pm_tunai']    ?? '1') === '1',
            'qris'     => ($s['pm_qris']     ?? '1') === '1',
            'transfer' => ($s['pm_transfer'] ?? '1') === '1',
            'gateway'  => ($s['pm_gateway']  ?? '0') === '1',
        ];

        $gatewayConfigured = !empty($s['midtrans_server_key'])
            && Setting::get('midtrans_enabled') === '1';

        return view('owner.payment-methods', compact('methods', 'enabled', 'gatewayConfigured'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user    = auth()->user();
        $ownerId = $user->ownerAccount()?->id ?? $user->id;

        // Tunai selalu aktif, tidak bisa dimatikan
        OwnerSetting::set('pm_tunai',    '1',                                               $ownerId);
        OwnerSetting::set('pm_qris',     $request->boolean('pm_qris')     ? '1' : '0',     $ownerId);
        OwnerSetting::set('pm_transfer', $request->boolean('pm_transfer') ? '1' : '0',     $ownerId);
        OwnerSetting::set('pm_gateway',  $request->boolean('pm_gateway')  ? '1' : '0',     $ownerId);

        ActivityLog::record('update_payment_methods', 'Metode pembayaran diperbarui.');

        return back()->with('success', 'Metode pembayaran berhasil disimpan.');
    }

    /** Helper: ambil array metode aktif untuk owner tertentu */
    public static function activeFor(int $ownerId): array
    {
        $s       = OwnerSetting::getForOwner($ownerId);
        $active  = [];

        if (($s['pm_tunai']    ?? '1') === '1') $active[] = 'tunai';
        if (($s['pm_qris']     ?? '1') === '1') $active[] = 'qris';
        if (($s['pm_transfer'] ?? '1') === '1') $active[] = 'transfer';
        if (($s['pm_gateway']  ?? '0') === '1'
            && !empty($s['midtrans_server_key'])
            && Setting::get('midtrans_enabled') === '1') {
            $active[] = 'gateway';
        }

        return $active;
    }
}
