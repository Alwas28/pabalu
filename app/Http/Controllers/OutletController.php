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

    public function create(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.create')) {
            abort(403);
        }

        $outletTypes = OutletType::where('is_active', true)
            ->where('slug', '!=', 'lainnya')
            ->orderBy('sort_order')
            ->get();

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
        ]);

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
            ($user->role !== 'admin' && $outlet->owner_id !== $user->id)) {
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
        ]);

        $outlet->update($data);

        return back()->with('success', 'Outlet berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission('outlet.delete') ||
            ($user->role !== 'admin' && $outlet->owner_id !== $user->id)) {
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
            ($user->role !== 'admin' && $outlet->owner_id !== $user->id)) {
            abort(403);
        }

        $outlet->update(['is_active' => !$outlet->is_active]);
        $status = $outlet->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Outlet berhasil {$status}.");
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
