<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $with = ['owner', 'outletType', 'province', 'regency', 'district'];
        $outlets = match ($user->role) {
            'admin'  => Outlet::with($with)->latest()->get(),
            'owner'  => $user->outlets()->with($with)->latest()->get(),
            default  => $user->assignedOutlets()->with($with)->get(),
        };

        $outletTypes = OutletType::where('is_active', true)
            ->where('slug', '!=', 'lainnya')
            ->orderBy('sort_order')
            ->get();

        $provinces = \App\Models\Province::orderBy('name')->get();

        return view('outlets.index', compact('outlets', 'outletTypes', 'user', 'provinces'));
    }

    public function create(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.create')) {
            abort(403);
        }

        if ($message = $this->outletLimitMessage($user)) {
            return redirect()->route('outlets.index')->with('error', $message);
        }

        $outletTypes = OutletType::where('is_active', true)
            ->where('slug', '!=', 'lainnya')
            ->orderBy('sort_order')
            ->get();

        // Sembunyikan jenis outlet yang tidak diizinkan paket owner dari pilihan —
        // supaya form tidak bisa "ditipu" pilih jenis yang nanti ditolak saat submit.
        if ($user->role !== 'admin') {
            $plan = $user->currentProPlan();
            $outletTypes = $outletTypes->filter(fn ($t) => $plan->allowsOutletType($t->id))->values();

            if ($outletTypes->isEmpty()) {
                return redirect()->route('outlets.index')
                    ->with('error', "Paket Anda saat ini ({$plan->name}) tidak mengizinkan jenis outlet apapun untuk dibuat. Hubungi admin.");
            }
        }

        $provinces = \App\Models\Province::orderBy('name')->get();

        return view('outlets.create', compact('outletTypes', 'provinces'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.create')) {
            abort(403);
        }

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:150'],
            'outlet_type_id'         => ['required', 'exists:outlet_types,id'],
            'bidang_usaha'           => [
                'nullable', 'string', 'max:100',
                \Illuminate\Validation\Rule::requiredIf(function () use ($request) {
                    $type = \App\Models\OutletType::find($request->outlet_type_id);
                    return $type && $type->route_prefix === 'retail';
                }),
            ],
            'address'                => ['nullable', 'string', 'max:500'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'order_mode'             => ['nullable', 'in:quick,kitchen'],
            'enable_opening_shift'   => ['nullable', 'boolean'],
            'enable_barcode_scanner' => ['nullable', 'boolean'],
            'province_id'            => ['nullable', 'exists:provinces,id'],
            'regency_id'             => ['nullable', 'exists:regencies,id'],
            'district_id'            => ['nullable', 'exists:districts,id'],
            'kelurahan'              => ['nullable', 'string', 'max:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($message = $this->outletLimitMessage($user)) {
            return back()->withInput()->with('error', $message);
        }
        if ($message = $this->outletTypeRestrictionMessage($user, (int) $data['outlet_type_id'])) {
            return back()->withInput()->with('error', $message);
        }

        $data['owner_id']  = $user->id;
        $data['is_active'] = true;
        $data['code']      = $this->generateOutletCode();
        $data['enable_opening_shift']   = $request->boolean('enable_opening_shift');
        $data['enable_barcode_scanner'] = $request->boolean('enable_barcode_scanner');

        $outlet = Outlet::create($data);
        $outlet->load('outletType');

        return redirect($outlet->route('show'))->with('success', 'Outlet berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.edit') ||
            ($user->role !== 'admin' && (int) $outlet->owner_id !== (int) $user->id)) {
            abort(403);
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'outlet_type_id' => ['required', 'exists:outlet_types,id'],
            'address'        => ['nullable', 'string', 'max:500'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'order_mode'     => ['nullable', 'in:quick,kitchen'],
            'province_id'    => ['nullable', 'exists:provinces,id'],
            'regency_id'     => ['nullable', 'exists:regencies,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'kelurahan'      => ['nullable', 'string', 'max:100'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $outlet->update($data);

        return back()->with('success', 'Outlet berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.delete') ||
            ($user->role !== 'admin' && (int) $outlet->owner_id !== (int) $user->id)) {
            abort(403);
        }

        $outlet->delete();

        return back()->with('success', 'Outlet berhasil dihapus.');
    }

    public function toggleActive(Outlet $outlet): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.edit') ||
            ($user->role !== 'admin' && (int) $outlet->owner_id !== (int) $user->id)) {
            abort(403);
        }

        $outlet->update(['is_active' => !$outlet->is_active]);
        $status = $outlet->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Outlet berhasil {$status}.");
    }

    // Batas JUMLAH TOTAL outlet per Paket Pro owner (semua jenis dihitung sama),
    // ditambah kuota tambahan khusus owner ini (extra_outlet_quota, diatur admin per-owner
    // lewat halaman Profil Owner) — dipakai kalau owner butuh lebih dari batas paketnya
    // tanpa harus upgrade paket. Dicek di create() (supaya form tidak bisa dibuka sama
    // sekali) dan store() (jaga-jaga kalau limit berubah/tercapai di antara buka form & submit).
    private function outletLimitMessage(User $user): ?string
    {
        if ($user->role === 'admin') {
            return null;
        }

        $plan = $user->currentProPlan();
        $maxOutlets = $user->effectiveOutletLimit();
        if ($maxOutlets === null) {
            return null;
        }

        if ($user->outlets()->count() >= $maxOutlets) {
            return "Paket Anda saat ini ({$plan->name}) hanya boleh punya {$maxOutlets} outlet. Upgrade paket atau hubungi admin untuk menambah outlet baru.";
        }

        return null;
    }

    // Batas JENIS outlet yang boleh dibuat per Paket Pro owner (mis. paket usage-based
    // yang cuma diizinkan admin untuk jenis outlet tertentu). Kosong = tidak dibatasi.
    private function outletTypeRestrictionMessage(User $user, int $outletTypeId): ?string
    {
        if ($user->role === 'admin') {
            return null;
        }

        $plan = $user->currentProPlan();
        if ($plan->allowsOutletType($outletTypeId)) {
            return null;
        }

        return "Paket Anda saat ini ({$plan->name}) tidak mengizinkan jenis outlet ini. Hubungi admin untuk info lebih lanjut.";
    }

    private function generateOutletCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }
}
