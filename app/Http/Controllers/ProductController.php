<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $user             = auth()->user();
        $outlets          = $user->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $accessibleIds    = $user->accessibleOutlets()->pluck('id');
        $assignedOutletId = $user->assignedOutletId();

        $query = Product::with(['outlet', 'category'])
            ->whereIn('outlet_id', $accessibleIds);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $outletId = $assignedOutletId ?? ($request->get('outlet_id') ?: null);
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('is_active', $request->get('status') === '1');
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('nama')->get();

        $baseStats = Product::whereIn('outlet_id', $accessibleIds);
        $stats = [
            'total'    => (clone $baseStats)->count(),
            'aktif'    => (clone $baseStats)->where('is_active', true)->count(),
            'nonaktif' => (clone $baseStats)->where('is_active', false)->count(),
        ];

        return view('products.index', compact('products', 'outlets', 'categories', 'stats', 'outletId', 'assignedOutletId'));
    }

    public function create(): View
    {
        $outlets    = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $categories = Category::where('is_active', true)->orderBy('nama')->get();
        return view('products.create', compact('outlets', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id'   => ['required', 'exists:outlets,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'kode'        => ['nullable', 'string', 'max:50'],
            'nama'        => ['required', 'string', 'max:200'],
            'deskripsi'   => ['nullable', 'string'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'satuan'      => ['required', 'string', 'max:30'],
            'is_active'   => ['boolean'],
            'gambar'      => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $product = Product::create($validated);

        ActivityLog::record('create_product',
            "Produk \"{$product->nama}\" (Rp " . number_format($product->harga_jual, 0, ',', '.') . ") dibuat.",
            $product
        );

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$product->nama}\" berhasil ditambahkan.");
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);
        $outlets    = auth()->user()->accessibleOutlets()->where('is_active', true)->orderBy('nama')->get();
        $categories = Category::where('is_active', true)->orderBy('nama')->get();
        return view('products.edit', compact('product', 'outlets', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $validated = $request->validate([
            'outlet_id'   => ['required', 'exists:outlets,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'kode'        => ['nullable', 'string', 'max:50'],
            'nama'        => ['required', 'string', 'max:200'],
            'deskripsi'   => ['nullable', 'string'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'satuan'      => ['required', 'string', 'max:30'],
            'is_active'   => ['boolean'],
            'gambar'      => ['nullable', 'image', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('gambar')) {
            if ($product->gambar) Storage::disk('public')->delete($product->gambar);
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            if ($product->gambar) Storage::disk('public')->delete($product->gambar);
            $validated['gambar'] = null;
        }

        $product->update($validated);

        ActivityLog::record('update_product',
            "Produk \"{$product->nama}\" diperbarui.",
            $product
        );

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$product->nama}\" berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        if ($product->gambar) Storage::disk('public')->delete($product->gambar);
        $nama = $product->nama;

        ActivityLog::record('delete_product', "Produk \"{$nama}\" dihapus.");

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus.");
    }

    private function authorizeProduct(Product $product): void
    {
        $ids = auth()->user()->accessibleOutlets()->pluck('id');
        if (!$ids->contains($product->outlet_id)) {
            abort(403);
        }
    }
}
