<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutletType;
use App\Models\ProFeature;
use App\Models\ProOwnerSubscription;
use App\Models\ProPlan;
use App\Models\ProRedemptionCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProAdminController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola Paket Pro.');
        }
    }

    public function features(): View
    {
        $this->authorizeAdmin();

        $features = ProFeature::orderBy('outlet_type')->orderBy('sort_order')->get();

        return view('admin.pro.index', [
            'tab'         => 'fitur',
            'features'    => $features,
            'outletTypes' => ProFeature::OUTLET_TYPES,
        ]);
    }

    public function plans(): View
    {
        $this->authorizeAdmin();

        $plans       = ProPlan::with(['features', 'allowedOutletTypes'])->orderBy('sort_order')->orderBy('id')->get();
        $allFeatures = ProFeature::orderBy('outlet_type')->orderBy('sort_order')->get();
        $allOutletTypes = OutletType::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.pro.index', [
            'tab'            => 'paket',
            'plans'          => $plans,
            'allFeatures'    => $allFeatures,
            'outletTypes'    => ProFeature::OUTLET_TYPES,
            'allOutletTypes' => $allOutletTypes,
        ]);
    }

    private function planValidationRules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:60'],
            'desc'                 => ['nullable', 'string', 'max:200'],
            'price'                => ['nullable', 'integer', 'min:0'],
            'max_outlet_types'     => ['nullable', 'integer', 'min:1'],
            'max_kasir'            => ['nullable', 'integer', 'min:1'],
            'features'             => ['nullable', 'array'],
            'features.*'           => ['string', 'exists:pro_features,slug'],
            'allowed_outlet_types'   => ['nullable', 'array'],
            'allowed_outlet_types.*' => ['integer', 'exists:outlet_types,id'],
        ];
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->planValidationRules());

        $slug = Str::slug($data['name']);
        if (ProPlan::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $plan = ProPlan::create([
            'name'                 => $data['name'],
            'slug'                 => $slug,
            'description'          => $data['desc'] ?? null,
            'price'                => $data['price'] ?? 0,
            'max_outlet_types'     => $data['max_outlet_types'] ?? null,
            'max_kasir'            => $data['max_kasir'] ?? null,
            'is_active'            => $request->boolean('is_active'),
            'is_default'           => false,
            'is_self_activatable'  => $request->boolean('is_self_activatable'),
            'sort_order'           => (int) (ProPlan::max('sort_order') ?? 0) + 1,
        ]);

        $plan->features()->sync(ProFeature::whereIn('slug', $data['features'] ?? [])->pluck('id'));
        $plan->allowedOutletTypes()->sync($data['allowed_outlet_types'] ?? []);

        return back()->with('success', "Paket \"{$plan->name}\" berhasil ditambahkan.");
    }

    public function updatePlan(Request $request, ProPlan $plan): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate($this->planValidationRules());

        $plan->update([
            'name'                 => $data['name'],
            'description'          => $data['desc'] ?? null,
            'price'                => $data['price'] ?? 0,
            'max_outlet_types'     => $data['max_outlet_types'] ?? null,
            'max_kasir'            => $data['max_kasir'] ?? null,
            'is_active'            => $request->boolean('is_active'),
            'is_self_activatable'  => $request->boolean('is_self_activatable'),
        ]);

        $plan->features()->sync(ProFeature::whereIn('slug', $data['features'] ?? [])->pluck('id'));
        $plan->allowedOutletTypes()->sync($data['allowed_outlet_types'] ?? []);

        return back()->with('success', "Paket \"{$plan->name}\" berhasil diperbarui.");
    }

    public function togglePlanActive(ProPlan $plan): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($plan->is_default && $plan->is_active) {
            return back()->with('error', 'Paket bawaan (Free) tidak bisa dinonaktifkan — selalu jadi fallback untuk owner baru.');
        }

        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Paket \"{$plan->name}\" berhasil {$status}." . (!$plan->is_active ? ' Owner tidak akan melihat paket ini lagi di halaman Langganan.' : ''));
    }

    public function destroyPlan(ProPlan $plan): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($plan->is_default) {
            return back()->with('error', 'Paket bawaan (Free) tidak bisa dihapus.');
        }
        if ($plan->redemptionCodes()->exists() || $plan->subscriptions()->exists()) {
            return back()->with('error', 'Paket ini tidak bisa dihapus karena sudah punya kode/pelanggan aktif. Nonaktifkan saja paketnya.');
        }

        $plan->delete();

        return back()->with('success', 'Paket berhasil dihapus.');
    }

    public function codes(): View
    {
        $this->authorizeAdmin();

        $codes = ProRedemptionCode::with(['plan', 'subscriptions.owner'])
            ->latest()
            ->get();

        $plans = ProPlan::where('is_active', true)->where('is_default', false)
            ->orderBy('sort_order')->get();

        return view('admin.pro.index', [
            'tab'   => 'kode',
            'codes' => $codes,
            'plans' => $plans,
        ]);
    }

    public function showCode(ProRedemptionCode $code): View
    {
        $this->authorizeAdmin();

        $code->load(['plan', 'creator', 'subscriptions' => function ($q) {
            $q->with('owner')->orderByDesc('activated_at');
        }]);

        return view('admin.pro.code-usage', ['code' => $code]);
    }

    // Hapus SATU pemakaian kode oleh owner tertentu — mencabut akses Pro-nya (kalau
    // ini pemakaian terbaru miliknya, otomatis kembali ke paket sebelumnya/Free lewat
    // User::currentProSubscription() yang memang ambil baris terbaru) dan mengembalikan
    // 1 slot pemakaian ke kode ini supaya bisa dipakai owner lain.
    public function destroyUsage(ProRedemptionCode $code, ProOwnerSubscription $subscription): RedirectResponse
    {
        $this->authorizeAdmin();

        if ((int) $subscription->pro_redemption_code_id !== $code->id) {
            abort(404);
        }

        $ownerName = $subscription->owner?->name ?? 'Owner ini';
        $subscription->delete();
        $code->decrement('uses_count');

        return back()->with('success', "Pemakaian kode oleh \"{$ownerName}\" berhasil dihapus. 1 slot pemakaian dikembalikan.");
    }

    public function storeCode(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'plan_id'    => ['required', 'exists:pro_plans,id'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'valid_days' => ['required', 'integer', 'min:1'],
            'max_uses'   => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $qty = $data['qty'] ?? 1;

        for ($i = 0; $i < $qty; $i++) {
            ProRedemptionCode::create([
                'code'        => $this->generateUniqueCode(),
                'pro_plan_id' => $data['plan_id'],
                'valid_days'  => $data['valid_days'],
                'max_uses'    => $data['max_uses'] ?? 1,
                'uses_count'  => 0,
                'expires_at'  => $data['expires_at'] ?? null,
                'is_active'   => true,
                'created_by'  => Auth::id(),
            ]);
        }

        return back()->with('success', "{$qty} kode aktivasi berhasil dibuat.");
    }

    public function updateCode(Request $request, ProRedemptionCode $code): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'max_uses' => ['required', 'integer', 'min:' . max(1, $code->uses_count)],
        ]);

        $code->update(['max_uses' => $data['max_uses']]);

        return back()->with('success', "Batas pemakaian kode \"{$code->code}\" berhasil diperbarui menjadi {$data['max_uses']}.");
    }

    // Hapus kode itu sendiri. Owner yang SUDAH memakainya TIDAK ikut kehilangan akses
    // Pro-nya — pro_owner_subscriptions.pro_redemption_code_id cuma di-null-kan
    // (lihat kolomnya: nullOnDelete), riwayat langganan mereka tetap ada.
    public function destroyCode(ProRedemptionCode $code): RedirectResponse
    {
        $this->authorizeAdmin();

        $codeText = $code->code;
        $code->delete();

        return back()->with('success', "Kode \"{$codeText}\" berhasil dihapus.");
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'PABALU-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        } while (ProRedemptionCode::where('code', $code)->exists());

        return $code;
    }
}
