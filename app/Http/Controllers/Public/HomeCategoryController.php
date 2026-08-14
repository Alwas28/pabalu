<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomeCategory;
use App\Models\Product;
use Illuminate\View\View;

class HomeCategoryController extends Controller
{
    /** Tampilkan semua produk dari kategori-kategori yang tergabung dalam satu HomeCategory. */
    public function show(HomeCategory $homeCategory): View
    {
        abort_unless($homeCategory->is_active, 404);

        $categoryIds = $homeCategory->categories()->pluck('categories.id');

        $products = Product::where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
            ->with('outlet.outletType', 'category')
            ->orderBy('name')
            ->limit(60)
            ->get();

        return view('home-categories.show', compact('homeCategory', 'products'));
    }
}
