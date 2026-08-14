<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /** Tampilkan semua produk pada satu kategori produk (bukan gabungan seperti HomeCategory). */
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $products = Product::where('is_active', true)
            ->where('category_id', $category->id)
            ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
            ->with('outlet.outletType', 'category')
            ->orderBy('name')
            ->limit(60)
            ->get();

        return view('categories.show', compact('category', 'products'));
    }
}
