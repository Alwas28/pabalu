<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    private function checkOutletAccess(Request $request, int $outletId): ?JsonResponse
    {
        $ok = $request->user()->accessibleOutlets()->where('id', $outletId)->exists();
        return $ok ? null : response()->json(['message' => 'Tidak punya akses ke outlet ini.'], 403);
    }

    // ── Daftar Produk (untuk POS) ─────────────────────────
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $tanggal = today()->toDateString();

        $products = Product::where('outlet_id', $request->outlet_id)
            ->where('is_active', true)
            ->with('category:id,nama')
            ->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'nama'        => $p->nama,
                'harga'       => (int) $p->harga_jual,
                'stok'        => StockMovement::currentStock($request->outlet_id, $p->id, $tanggal),
                'category_id' => $p->category_id,
                'category'    => $p->category?->nama,
                'foto'        => $p->foto ? asset('storage/' . $p->foto) : null,
            ]);

        $categories = $products->pluck('category')->filter()->unique()->values();

        return response()->json([
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    // ── Semua Produk (untuk kelola) ───────────────────────
    public function manage(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'q'         => ['nullable', 'string'],
        ]);

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $products = Product::with('category:id,nama')
            ->where('outlet_id', $request->outlet_id)
            ->when($request->q, fn($q, $s) => $q->where(fn($inner) =>
                $inner->where('nama', 'like', "%{$s}%")->orWhere('kode', 'like', "%{$s}%")
            ))
            ->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'kode'        => $p->kode,
                'nama'        => $p->nama,
                'harga'       => (int) $p->harga_jual,
                'satuan'      => $p->satuan,
                'is_active'   => $p->is_active,
                'category_id' => $p->category_id,
                'category'    => $p->category?->nama,
                'foto'        => $p->gambar ? asset('storage/' . $p->gambar) : null,
                'deskripsi'   => $p->deskripsi,
            ]);

        $categories = Category::where('is_active', true)->orderBy('nama')->get(['id', 'nama']);

        return response()->json(['products' => $products, 'categories' => $categories]);
    }

    // ── Tambah Produk ─────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
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

        if ($err = $this->checkOutletAccess($request, $request->outlet_id)) return $err;

        $data = $request->only(['outlet_id', 'category_id', 'kode', 'nama', 'deskripsi', 'harga_jual', 'satuan', 'is_active']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $product = Product::create($data);

        ActivityLog::record('create_product',
            "Produk \"{$product->nama}\" dibuat via API.",
            $product
        );

        return response()->json(['message' => "Produk \"{$product->nama}\" berhasil ditambahkan.", 'id' => $product->id], 201);
    }

    // ── Update Produk ─────────────────────────────────────
    public function update(Request $request, Product $product): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $product->outlet_id)) return $err;

        $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'kode'        => ['nullable', 'string', 'max:50'],
            'nama'        => ['required', 'string', 'max:200'],
            'deskripsi'   => ['nullable', 'string'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'satuan'      => ['required', 'string', 'max:30'],
            'is_active'   => ['boolean'],
            'gambar'      => ['nullable', 'image', 'max:2048'],
            'hapus_gambar'=> ['nullable', 'boolean'],
        ]);

        $data = $request->only(['category_id', 'kode', 'nama', 'deskripsi', 'harga_jual', 'satuan', 'is_active']);

        if ($request->hasFile('gambar')) {
            if ($product->gambar) Storage::disk('public')->delete($product->gambar);
            $data['gambar'] = $request->file('gambar')->store('products', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            if ($product->gambar) Storage::disk('public')->delete($product->gambar);
            $data['gambar'] = null;
        }

        $product->update($data);

        ActivityLog::record('update_product', "Produk \"{$product->nama}\" diperbarui via API.", $product);

        return response()->json(['message' => "Produk \"{$product->nama}\" berhasil diperbarui."]);
    }

    // ── Hapus Produk ──────────────────────────────────────
    public function destroy(Request $request, Product $product): JsonResponse
    {
        if ($err = $this->checkOutletAccess($request, $product->outlet_id)) return $err;

        if ($product->gambar) Storage::disk('public')->delete($product->gambar);
        $nama = $product->nama;

        ActivityLog::record('delete_product', "Produk \"{$nama}\" dihapus via API.");

        $product->delete();

        return response()->json(['message' => "Produk \"{$nama}\" berhasil dihapus."]);
    }
}
