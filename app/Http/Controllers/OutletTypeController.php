<?php

namespace App\Http\Controllers;

use App\Models\OutletType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OutletTypeController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola jenis outlet.');
        }
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $types = OutletType::withCount('outlets')->orderBy('sort_order')->orderBy('name')->get();

        return view('outlet-types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'type_code'   => ['nullable', 'string', 'size:2', 'unique:outlet_types,type_code'],
            'icon'        => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        OutletType::create([
            'name'                   => $validated['name'],
            'slug'                   => $this->makeSlug($validated['name']),
            'type_code'              => $validated['type_code'] ? strtoupper($validated['type_code']) : null,
            'icon'                   => $validated['icon'] ?: 'fa-store',
            'description'            => $validated['description'] ?? null,
            'requires_opening_stock' => $request->boolean('requires_opening_stock'),
            'track_cogs'             => $request->boolean('track_cogs'),
            'is_active'              => true,
            'sort_order'             => $validated['sort_order'] ?? 0,
        ]);

        return back()->with('success', "Jenis outlet \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, OutletType $outletType): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'type_code'   => ['nullable', 'string', 'size:2', "unique:outlet_types,type_code,{$outletType->id}"],
            'icon'        => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $outletType->update([
            'name'                   => $validated['name'],
            'type_code'              => $validated['type_code'] ? strtoupper($validated['type_code']) : null,
            'icon'                   => $validated['icon'] ?: 'fa-store',
            'description'            => $validated['description'] ?? null,
            'requires_opening_stock' => $request->boolean('requires_opening_stock'),
            'track_cogs'             => $request->boolean('track_cogs'),
            'sort_order'             => $validated['sort_order'] ?? 0,
        ]);

        return back()->with('success', "Jenis outlet \"{$outletType->name}\" berhasil diperbarui.");
    }

    public function destroy(OutletType $outletType): RedirectResponse
    {
        $this->authorizeAdmin();

        $count = $outletType->outlets()->count();
        if ($count > 0) {
            return back()->with('error', "Jenis outlet \"{$outletType->name}\" tidak dapat dihapus karena masih digunakan oleh {$count} outlet.");
        }

        $name = $outletType->name;
        $outletType->delete();

        return back()->with('success', "Jenis outlet \"{$name}\" berhasil dihapus.");
    }

    public function toggleActive(OutletType $outletType): RedirectResponse
    {
        $this->authorizeAdmin();

        $outletType->update(['is_active' => !$outletType->is_active]);
        $status = $outletType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Jenis outlet \"{$outletType->name}\" berhasil {$status}.");
    }

    private function makeSlug(string $name): string
    {
        $base = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace([' ', '-', '/'], '_', $name)));
        $slug = $base;
        $i    = 1;
        while (OutletType::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }
}
