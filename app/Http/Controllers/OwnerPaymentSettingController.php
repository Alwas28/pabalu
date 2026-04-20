<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OwnerSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPaymentSettingController extends Controller
{
    public function index(): View
    {
        $user    = auth()->user();
        $ownerId = $user->ownerAccount()?->id ?? $user->id;
        $settings = OwnerSetting::getForOwner($ownerId);

        $hasKeys  = !empty($settings['midtrans_server_key']);
        $isActive = $hasKeys && ($settings['midtrans_enabled'] ?? '0') === '1';

        $adminWaRaw = \App\Models\User::role('admin')->with('profile')->first()?->profile?->no_hp;
        $adminWa    = $adminWaRaw
            ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', ltrim($adminWaRaw, '0'))
            : null;
        $adminWa    = $adminWa ? str_replace('wa.me/0', 'wa.me/62', $adminWa) : null;

        return view('owner.payment-settings', compact('settings', 'hasKeys', 'isActive', 'adminWa'));
    }
}
