<?php

namespace App\Http\Controllers;

use App\Models\ProOwnerSubscription;
use App\Models\ProPlan;
use App\Models\ProRedemptionCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProSubscriptionController extends Controller
{
    private function authorizeOwner(): User
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role !== 'owner') {
            abort(403, 'Hanya pemilik toko yang punya halaman langganan.');
        }

        return $user;
    }

    public function show(): View
    {
        $owner = $this->authorizeOwner();

        $sub  = $owner->currentProSubscription()->with('plan')->first();
        $plan = $sub?->plan ?? ProPlan::where('is_default', true)->first();

        $subscription = [
            'plan_name'        => $plan->name,
            'max_outlet_types' => $plan->max_outlet_types,
            'max_kasir'        => $plan->max_kasir,
            'is_free'          => $plan->is_default,
            'started_at'       => $sub?->activated_at?->toDateString(),
            'expires_at'       => $sub?->expires_at?->toDateString(),
            'days_left'        => $sub?->expires_at ? max(0, (int) now()->diffInDays($sub->expires_at, false)) : null,
        ];

        $history = $owner->proSubscriptions()
            ->with(['plan', 'redemptionCode'])
            ->orderByDesc('activated_at')
            ->get()
            ->filter(fn ($row) => $row->redemptionCode !== null)
            ->map(fn ($row) => [
                'code'        => $row->redemptionCode->code,
                'plan'        => $row->plan->name,
                'redeemed_at' => $row->activated_at->toDateString(),
            ]);

        $plans = ProPlan::where('is_active', true)->with('allowedOutletTypes')->orderBy('sort_order')->get();

        return view('pro.subscription', [
            'subscription'  => $subscription,
            'history'       => $history,
            'plans'         => $plans,
            'currentPlanId' => $plan->id,
            'owner'         => $owner,
        ]);
    }

    public function redeem(Request $request): RedirectResponse
    {
        $owner = $this->authorizeOwner();

        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);

        $code = ProRedemptionCode::where('code', strtoupper(trim($data['code'])))->first();

        if (!$code || !$code->isRedeemable()) {
            return back()->with('error', 'Kode tidak ditemukan, sudah kadaluarsa, atau sudah habis dipakai.');
        }

        DB::transaction(function () use ($owner, $code) {
            $current = $owner->currentProSubscription;
            $base    = ($current?->expires_at && $current->expires_at->isFuture()) ? $current->expires_at : now();

            ProOwnerSubscription::create([
                'owner_id'               => $owner->id,
                'pro_plan_id'            => $code->pro_plan_id,
                'pro_redemption_code_id' => $code->id,
                'activated_at'           => now(),
                'expires_at'             => $base->copy()->addDays($code->valid_days),
            ]);

            $code->increment('uses_count');
        });

        return back()->with('success', "Kode berhasil diaktifkan! Paket \"{$code->plan->name}\" sekarang aktif.");
    }

    // Aktivasi mandiri TANPA kode — hanya untuk paket yang ditandai admin sebagai
    // is_self_activatable (mis. paket usage-based yang ditagih belakangan lewat Tagihan
    // Outlet, bukan bayar di muka). expires_at dibiarkan null = aktif permanen sampai
    // admin ubah manual (tidak seperti paket via kode yang punya masa berlaku).
    public function activate(ProPlan $plan): RedirectResponse
    {
        $owner = $this->authorizeOwner();

        if (!$plan->is_active || !$plan->is_self_activatable) {
            abort(403, 'Paket ini tidak bisa diaktifkan sendiri.');
        }

        ProOwnerSubscription::create([
            'owner_id'               => $owner->id,
            'pro_plan_id'            => $plan->id,
            'pro_redemption_code_id' => null,
            'activated_at'           => now(),
            'expires_at'             => null,
        ]);

        return back()->with('success', "Paket \"{$plan->name}\" berhasil diaktifkan.");
    }
}
