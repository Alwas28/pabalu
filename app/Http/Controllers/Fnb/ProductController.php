<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
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

    /** Aturan validasi berbeda-beda per jenis outlet */
    private function rules(Outlet $outlet, bool $isUpdate = false): array
    {
        $trackCogs = $outlet->outletType?->track_cogs ?? false;

        $rules = [
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku'         => ['nullable', 'string', 'max:60'],
            'unit'        => ['required', 'string', 'max:30'],
            'price'       => ['required', 'integer', 'min:0'],
            'min_stock'   => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($trackCogs) {
            $rules['cost_price'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $products   = $outlet->products()->with('category')->get();
        $categories = $outlet->categories()->where('is_active', true)->get();
        $trackCogs  = $outlet->outletType?->track_cogs ?? false;

        return view('fnb.products.index', compact('outlet', 'user', 'products', 'categories', 'trackCogs'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('product.create')) {
            abort(403);
        }

        $data = $request->validate($this->rules($outlet));

        // Verify category belongs to this outlet
        if (!empty($data['category_id'])) {
            $outlet->categories()->findOrFail($data['category_id']);
        }

        $data['outlet_id'] = $outlet->id;
        $data['min_stock'] = $data['min_stock'] ?? 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store("products/{$outlet->id}", 'public');
        }

        Product::create($data);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet, Product $product): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('product.edit')) {
            abort(403);
        }

        if ($product->outlet_id !== $outlet->id) {
            abort(404);
        }

        $data = $request->validate($this->rules($outlet, true));

        if (!empty($data['category_id'])) {
            $outlet->categories()->findOrFail($data['category_id']);
        }

        $data['min_stock'] = $data['min_stock'] ?? 0;

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store("products/{$outlet->id}", 'public');
        }

        // Hapus gambar jika diminta
        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $data['image'] = null;
        }

        $product->update($data);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, Product $product): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('product.delete')) {
            abort(403);
        }

        if ($product->outlet_id !== $outlet->id) {
            abort(404);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleActive(Outlet $outlet, Product $product): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('product.edit')) {
            abort(403);
        }

        if ($product->outlet_id !== $outlet->id) {
            abort(404);
        }

        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Produk berhasil {$status}.");
    }
}
