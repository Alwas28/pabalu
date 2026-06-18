<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WilayahController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') abort(403);
    }

    // ── Provinces ──────────────────────────────────────────
    public function index(): View
    {
        $this->authorizeAdmin();
        $provinces = Province::withCount('regencies')->orderBy('name')->get();
        return view('wilayah.index', compact('provinces'));
    }

    public function storeProvince(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:provinces,name']]);
        Province::create($d);
        return back()->with('success', 'Provinsi berhasil ditambahkan.');
    }

    public function updateProvince(Request $request, Province $province): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:provinces,name,' . $province->id]]);
        $province->update($d);
        return back()->with('success', 'Provinsi berhasil diperbarui.');
    }

    public function destroyProvince(Province $province): RedirectResponse
    {
        $this->authorizeAdmin();
        $province->delete();
        return back()->with('success', 'Provinsi berhasil dihapus.');
    }

    // ── Regencies ──────────────────────────────────────────
    public function showProvince(Province $province): View
    {
        $this->authorizeAdmin();
        $regencies = $province->regencies()->withCount('districts')->orderBy('name')->get();
        return view('wilayah.province', compact('province', 'regencies'));
    }

    public function storeRegency(Request $request, Province $province): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:kabupaten,kota'],
        ]);
        $province->regencies()->create($d);
        return back()->with('success', 'Kabupaten/Kota berhasil ditambahkan.');
    }

    public function updateRegency(Request $request, Province $province, Regency $regency): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:kabupaten,kota'],
        ]);
        $regency->update($d);
        return back()->with('success', 'Kabupaten/Kota berhasil diperbarui.');
    }

    public function destroyRegency(Province $province, Regency $regency): RedirectResponse
    {
        $this->authorizeAdmin();
        $regency->delete();
        return back()->with('success', 'Kabupaten/Kota berhasil dihapus.');
    }

    // ── Districts ──────────────────────────────────────────
    public function showRegency(Province $province, Regency $regency): View
    {
        $this->authorizeAdmin();
        $districts = $regency->districts()->orderBy('name')->get();
        return view('wilayah.regency', compact('province', 'regency', 'districts'));
    }

    public function storeDistrict(Request $request, Province $province, Regency $regency): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $regency->districts()->create($d);
        return back()->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function updateDistrict(Request $request, Province $province, Regency $regency, District $district): RedirectResponse
    {
        $this->authorizeAdmin();
        $d = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $district->update($d);
        return back()->with('success', 'Kecamatan berhasil diperbarui.');
    }

    public function destroyDistrict(Province $province, Regency $regency, District $district): RedirectResponse
    {
        $this->authorizeAdmin();
        $district->delete();
        return back()->with('success', 'Kecamatan berhasil dihapus.');
    }

    // ── API JSON untuk cascading dropdown ─────────────────
    public function apiRegencies(Request $request): JsonResponse
    {
        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get(['id', 'name', 'type'])
            ->map(fn($r) => ['id' => $r->id, 'name' => $r->full_name]);
        return response()->json($regencies);
    }

    public function apiDistricts(Request $request): JsonResponse
    {
        $districts = District::where('regency_id', $request->regency_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($districts);
    }
}
