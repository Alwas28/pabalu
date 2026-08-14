<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OutletType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutletTypeCategoryController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola kategori jenis outlet.');
        }
    }

    public function store(Request $request, OutletType $outletType): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'icon'        => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['outlet_type_id'] = $outletType->id;
        $data['icon']           = $data['icon'] ?: 'fa-tag';
        $data['sort_order']     = $data['sort_order'] ?? 0;
        $data['is_featured']    = $request->boolean('is_featured');

        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, OutletType $outletType, Category $category): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($category->outlet_type_id != $outletType->id) abort(404);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'icon'        => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['icon']        = $data['icon'] ?: 'fa-tag';
        $data['sort_order']  = $data['sort_order'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(OutletType $outletType, Category $category): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($category->outlet_type_id != $outletType->id) abort(404);

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleActive(OutletType $outletType, Category $category): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($category->outlet_type_id != $outletType->id) abort(404);

        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori berhasil {$status}.");
    }
}
