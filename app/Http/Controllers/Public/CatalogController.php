<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(string $code): View
    {
        $outlet = Outlet::where('code', strtoupper($code))
            ->where('is_active', true)
            ->with(['outletType'])
            ->firstOrFail();

        $categories = $outlet->categories()
            ->where('is_active', true)
            ->with(['products' => fn($q) => $q
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn($c) => $c->products->isNotEmpty());

        $uncategorized = $outlet->products()
            ->where('is_active', true)
            ->whereNull('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.katalog.index', compact('outlet', 'categories', 'uncategorized'));
    }
}
