<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $outletIds = $user->isAdmin() ? null : $user->accessibleOutlets()->pluck('id');

        $query = Category::withCount(['products' => function ($q) use ($outletIds) {
            if ($outletIds !== null) {
                $q->whereIn('outlet_id', $outletIds);
            }
        }]);

        if ($search = $request->get('q')) {
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('is_active', $request->get('status') === '1');
        }

        $categories = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'    => Category::count(),
            'aktif'    => Category::where('is_active', true)->count(),
            'nonaktif' => Category::where('is_active', false)->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:categories,nama'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $category = Category::create($validated);

        ActivityLog::record('create_category', "Kategori \"{$category->nama}\" dibuat.", $category);

        return redirect()->route('categories.index')
            ->with('success', "Kategori \"{$category->nama}\" berhasil ditambahkan.");
    }

    public function edit(Category $category): View
    {
        $category->loadCount('products');
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:categories,nama,' . $category->id],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $category->update($validated);

        ActivityLog::record('update_category', "Kategori \"{$category->nama}\" diperbarui.", $category);

        return redirect()->route('categories.index')
            ->with('success', "Kategori \"{$category->nama}\" berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', "Kategori \"{$category->nama}\" tidak dapat dihapus karena masih digunakan oleh produk.");
        }

        $nama = $category->nama;

        ActivityLog::record('delete_category', "Kategori \"{$nama}\" dihapus.");

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }
}
