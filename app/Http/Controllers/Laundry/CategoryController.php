<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    private function authorizeOutlet(Outlet $outlet): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        return $user;
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $categories = $outlet->categories()->get();

        return view('laundry.categories.index', compact('outlet', 'user', 'categories'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('category.create')) {
            abort(403);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['outlet_id']  = $outlet->id;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet, Category $category): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('category.edit')) {
            abort(403);
        }

        if ($category->outlet_id !== $outlet->id) {
            abort(404);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, Category $category): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('category.delete')) {
            abort(403);
        }

        if ($category->outlet_id !== $outlet->id) {
            abort(404);
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleActive(Outlet $outlet, Category $category): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('category.edit')) {
            abort(403);
        }

        if ($category->outlet_id !== $outlet->id) {
            abort(404);
        }

        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori berhasil {$status}.");
    }
}
