<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OwnerSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerAccountController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = User::role('owner')->with(['profile', 'ownedOutlets']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filter = $request->get('status')) {
            match ($filter) {
                'trial'    => $query->where('account_type', 'trial'),
                'premium'  => $query->where('account_type', 'premium'),
                'inactive' => $query->where('account_type', 'inactive'),
                'expired'  => $query->where('account_type', 'trial')
                                    ->where('trial_ends_at', '<', now()),
                default    => null,
            };
        }

        $owners = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'    => User::role('owner')->count(),
            'trial'    => User::role('owner')->where('account_type', 'trial')->where('trial_ends_at', '>=', now())->count(),
            'premium'  => User::role('owner')->where('account_type', 'premium')->count(),
            'expired'  => User::role('owner')->where('account_type', 'trial')->where('trial_ends_at', '<', now())->count(),
            'inactive' => User::role('owner')->where('account_type', 'inactive')->count(),
        ];

        return view('admin.owner-accounts', compact('owners', 'stats'));
    }

    public function setPremium(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $user->update([
            'account_type'  => 'premium',
            'trial_ends_at' => null,
        ]);

        ActivityLog::record('owner_set_premium',
            "Akun owner \"{$user->name}\" ({$user->email}) diubah ke Premium."
        );

        return back()->with('success', "Akun \"{$user->name}\" berhasil diubah ke Premium.");
    }

    public function setTrial(User $user, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $days = (int) $request->input('days', 30);

        $user->update([
            'account_type'  => 'trial',
            'trial_ends_at' => now()->addDays($days),
        ]);

        ActivityLog::record('owner_set_trial',
            "Akun owner \"{$user->name}\" ({$user->email}) diatur ulang trial {$days} hari."
        );

        return back()->with('success', "Trial \"{$user->name}\" diperpanjang {$days} hari.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $user->update(['account_type' => 'inactive']);

        ActivityLog::record('owner_deactivated',
            "Akun owner \"{$user->name}\" ({$user->email}) dinonaktifkan."
        );

        return back()->with('success', "Akun \"{$user->name}\" berhasil dinonaktifkan.");
    }

    public function setPaymentSettings(User $user, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $request->validate([
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
        ]);

        $enabled = $request->boolean('midtrans_enabled') ? '1' : '0';
        OwnerSetting::set('midtrans_enabled',       $enabled,                                    $user->id);
        OwnerSetting::set('midtrans_server_key',    $request->input('midtrans_server_key', ''),  $user->id);
        OwnerSetting::set('midtrans_client_key',    $request->input('midtrans_client_key', ''),  $user->id);
        OwnerSetting::set('midtrans_is_production', $request->boolean('midtrans_is_production') ? '1' : '0', $user->id);

        ActivityLog::record('admin_set_payment',
            "Konfigurasi Midtrans owner \"{$user->name}\" ({$user->email}) diperbarui oleh admin."
        );

        return back()->with('success', "Konfigurasi Midtrans \"{$user->name}\" berhasil disimpan.");
    }

    public function activate(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($user->isOwner(), 404);

        $user->update([
            'account_type'  => 'premium',
            'trial_ends_at' => null,
        ]);

        ActivityLog::record('owner_activated',
            "Akun owner \"{$user->name}\" ({$user->email}) diaktifkan kembali sebagai Premium."
        );

        return back()->with('success', "Akun \"{$user->name}\" berhasil diaktifkan kembali.");
    }
}
