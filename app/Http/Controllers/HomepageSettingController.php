<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomeCategory;
use App\Models\HomeMenu;
use App\Models\HomeSlider;
use App\Models\PromoBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomepageSettingController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengelola pengaturan homepage.');
        }
    }

    public function index(): RedirectResponse
    {
        $this->authorizeAdmin();

        return redirect()->route('settings.header');
    }

    public function header(): View
    {
        $this->authorizeAdmin();

        $sliders = HomeSlider::orderBy('sort_order')->orderBy('id')->get();

        return view('settings.index', ['tab' => 'header', 'sliders' => $sliders]);
    }

    public function menu(): View
    {
        $this->authorizeAdmin();

        $menus = HomeMenu::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $parents = HomeMenu::whereNull('parent_id')->orderBy('label')->get();

        return view('settings.index', ['tab' => 'menu', 'menus' => $menus, 'parents' => $parents]);
    }

    public function categories(): View
    {
        $this->authorizeAdmin();

        $homeCategories = HomeCategory::withCount('categories')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Kategori produk asli, dikelompokkan per jenis outlet, untuk daftar checklist.
        $categoriesByType = Category::with('outletType')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($c) => $c->outletType?->name ?? 'Lainnya');

        return view('settings.index', [
            'tab' => 'categories',
            'homeCategories' => $homeCategories,
            'categoriesByType' => $categoriesByType,
        ]);
    }

    public function promo(): View
    {
        $this->authorizeAdmin();

        $promoBanners = PromoBanner::orderBy('sort_order')->orderBy('id')->get();

        return view('settings.index', ['tab' => 'promo', 'promoBanners' => $promoBanners]);
    }
}
