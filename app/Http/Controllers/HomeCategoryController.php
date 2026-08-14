<?php

namespace App\Http\Controllers;

use App\Models\HomeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeCategoryController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola kategori homepage.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'description'  => ['nullable', 'string', 'max:255'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $homeCategory = HomeCategory::create([
            'name'        => $data['name'],
            'image'       => $request->hasFile('image') ? $request->file('image')->store('home-categories', 'public') : null,
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => true,
        ]);

        $homeCategory->categories()->sync($data['category_ids'] ?? []);

        return back()->with('success', "Kategori \"{$homeCategory->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, HomeCategory $homeCategory): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'description'  => ['nullable', 'string', 'max:255'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $updateData = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($homeCategory->image) {
                Storage::disk('public')->delete($homeCategory->image);
            }
            $updateData['image'] = $request->file('image')->store('home-categories', 'public');
        }

        $homeCategory->update($updateData);

        $homeCategory->categories()->sync($data['category_ids'] ?? []);

        return back()->with('success', "Kategori \"{$homeCategory->name}\" berhasil diperbarui.");
    }

    public function destroy(HomeCategory $homeCategory): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($homeCategory->image) {
            Storage::disk('public')->delete($homeCategory->image);
        }

        $name = $homeCategory->name;
        $homeCategory->delete();

        return back()->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function toggleActive(HomeCategory $homeCategory): RedirectResponse
    {
        $this->authorizeAdmin();

        $homeCategory->update(['is_active' => !$homeCategory->is_active]);
        $status = $homeCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori berhasil {$status}.");
    }
}
