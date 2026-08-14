<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q    = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', 'all');

        $outletTypeSlug = null;
        if (str_starts_with($type, 'outlet:')) {
            $outletTypeSlug = substr($type, 7);
        }

        $wantProducts = in_array($type, ['all', 'product'], true);
        $wantOutlets  = $type === 'all' || $type === 'outlet' || $outletTypeSlug !== null;

        $products = collect();
        $outlets  = collect();
        $fuzzy    = false;

        if ($q !== '' && $wantProducts) {
            $products = Product::where('is_active', true)
                ->where('name', 'like', "%{$q}%")
                ->whereHas('outlet', fn ($oq) => $oq->where('is_active', true))
                ->with('outlet.outletType')
                ->orderBy('name')
                ->limit(24)
                ->get();

            // Tidak ada hasil persis — coba cari yang mirip (typo-tolerant) sebelum menyerah.
            if ($products->isEmpty()) {
                $products = $this->fuzzyProducts($q);
                $fuzzy = $fuzzy || $products->isNotEmpty();
            }
        }

        if ($q !== '' && $wantOutlets) {
            $outlets = Outlet::where('is_active', true)
                ->where('name', 'like', "%{$q}%")
                ->when($outletTypeSlug, fn ($oq) => $oq->whereHas('outletType', fn ($tq) => $tq->where('slug', $outletTypeSlug)))
                ->with('outletType')
                ->orderBy('name')
                ->limit(24)
                ->get();

            if ($outlets->isEmpty()) {
                $outlets = $this->fuzzyOutlets($q, $outletTypeSlug);
                $fuzzy = $fuzzy || $outlets->isNotEmpty();
            }
        }

        return view('search.index', compact('q', 'type', 'products', 'outlets', 'wantProducts', 'wantOutlets', 'fuzzy'));
    }

    private function fuzzyProducts(string $q): Collection
    {
        $candidates = Product::where('is_active', true)
            ->whereHas('outlet', fn ($oq) => $oq->where('is_active', true))
            ->with('outlet.outletType')
            ->orderBy('name')
            ->limit(2000)
            ->get();

        return $this->rankByFuzzyMatch($candidates, $q, fn ($p) => $p->name)->take(24)->values();
    }

    private function fuzzyOutlets(string $q, ?string $outletTypeSlug): Collection
    {
        $candidates = Outlet::where('is_active', true)
            ->when($outletTypeSlug, fn ($oq) => $oq->whereHas('outletType', fn ($tq) => $tq->where('slug', $outletTypeSlug)))
            ->with('outletType')
            ->orderBy('name')
            ->limit(2000)
            ->get();

        return $this->rankByFuzzyMatch($candidates, $q, fn ($o) => $o->name)->take(24)->values();
    }

    /**
     * Filter & urutkan kandidat berdasarkan kemiripan kata (Levenshtein per-kata) terhadap query.
     * Menangani typo seperti "aym" ~ "ayam" tanpa perlu index/server pencarian terpisah.
     */
    private function rankByFuzzyMatch(Collection $candidates, string $q, callable $nameGetter): Collection
    {
        $qWords = array_values(array_filter(
            preg_split('/\s+/', mb_strtolower($q)),
            fn ($w) => mb_strlen($w) >= 3
        ));

        if (empty($qWords)) {
            return collect();
        }

        return $candidates
            ->map(function ($item) use ($qWords, $nameGetter) {
                $words = preg_split('/\s+/', mb_strtolower($nameGetter($item)));
                $best  = null;

                foreach ($qWords as $qw) {
                    foreach ($words as $w) {
                        if ($w === '') continue;
                        $threshold = mb_strlen($w) <= 5 ? 1 : 2;
                        $dist = levenshtein($qw, $w);
                        if ($dist <= $threshold && ($best === null || $dist < $best)) {
                            $best = $dist;
                        }
                    }
                }

                return ['item' => $item, 'dist' => $best];
            })
            ->filter(fn ($r) => $r['dist'] !== null)
            ->sortBy('dist')
            ->pluck('item');
    }
}
