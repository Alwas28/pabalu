<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
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

    // Merge system defaults + outlet custom categories (slug => label)
    private function resolveCategories(Outlet $outlet): array
    {
        $cats = Expense::$categories;

        foreach ($outlet->expenseCategories as $custom) {
            $cats[$custom->slug] = $custom->label;
        }

        return $cats;
    }

    public function index(Request $request, Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.view')) {
            abort(403);
        }

        $outlet->load(['outletType', 'expenseCategories']);

        // Monthly stats
        $thisMonth = $outlet->expenses()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->get();

        $statTotal = $thisMonth->sum('amount');
        $statCount = $thisMonth->count();
        $statAvg   = $statCount > 0 ? $statTotal / $statCount : 0;

        // Per-category breakdown (this month)
        $byCategory = $thisMonth->groupBy('category')->map(fn ($g) => $g->sum('amount'))->sortDesc();

        // Paginated list
        $expenses = $outlet->expenses()
            ->with('user')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('search'), fn ($q) => $q->where('description', 'like', '%'.$request->search.'%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $categories       = $this->resolveCategories($outlet);
        $customCategories = $outlet->expenseCategories;

        return view('sewa.expenses.index', compact(
            'outlet', 'user', 'expenses',
            'statTotal', 'statCount', 'statAvg',
            'byCategory', 'categories', 'customCategories'
        ));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.create')) {
            abort(403);
        }

        $outlet->load('expenseCategories');
        $allSlugs = implode(',', array_keys($this->resolveCategories($outlet)));

        $data = $request->validate([
            'date'        => ['required', 'date'],
            'category'    => ['required', 'string', "in:{$allSlugs}"],
            'description' => ['required', 'string', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:999999999'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $outlet->expenses()->create(array_merge($data, ['user_id' => $user->id]));

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(Request $request, Outlet $outlet, Expense $expense): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.edit')) {
            abort(403);
        }

        if ($expense->outlet_id != $outlet->id) {
            abort(404);
        }

        $outlet->load('expenseCategories');
        $allSlugs = implode(',', array_keys($this->resolveCategories($outlet)));

        $data = $request->validate([
            'date'        => ['required', 'date'],
            'category'    => ['required', 'string', "in:{$allSlugs}"],
            'description' => ['required', 'string', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:999999999'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $expense->update($data);

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, Expense $expense): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.delete')) {
            abort(403);
        }

        if ($expense->outlet_id != $outlet->id) {
            abort(404);
        }

        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    public function storeCategory(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.edit')) {
            abort(403);
        }

        $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $label    = trim($request->label);
        $baseSlug = preg_replace('/[^a-z0-9]+/', '_', strtolower($label));
        $baseSlug = trim($baseSlug, '_') ?: 'kategori';

        // Ensure slug does not conflict with system slugs or existing custom ones
        $slug = $baseSlug;
        $i    = 2;
        while (
            array_key_exists($slug, Expense::$categories) ||
            $outlet->expenseCategories()->where('slug', $slug)->exists()
        ) {
            $slug = $baseSlug . '_' . $i++;
        }

        $outlet->expenseCategories()->create([
            'slug'  => $slug,
            'label' => $label,
        ]);

        return back()->with('success', "Kategori \"{$label}\" berhasil ditambahkan.");
    }

    public function destroyCategory(Outlet $outlet, string $slug): RedirectResponse
    {
        $user = $this->authorizeOutlet($outlet);

        if (!$user->hasPermission('expense.edit')) {
            abort(403);
        }

        $outlet->expenseCategories()->where('slug', $slug)->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
