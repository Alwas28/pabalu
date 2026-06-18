<?php

namespace App\Http\Controllers\Retail;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PriceController extends Controller
{
    private function authorize(Outlet $outlet, Product $product): User
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$outlet->isAccessibleBy($user)) {
            abort(403);
        }

        if (!$outlet->isRetailFlow()) {
            abort(404);
        }

        if ($product->outlet_id !== $outlet->id) {
            abort(404);
        }

        return $user;
    }

    public function edit(Outlet $outlet, Product $product): View
    {
        $user = $this->authorize($outlet, $product);

        if (!$user->hasPermission('product.edit')) {
            abort(403);
        }

        $outlet->load('outletType');
        $histories = $product->priceHistories()->with('changedBy')->limit(30)->get();

        return view('retail.products.harga', compact('outlet', 'product', 'user', 'histories'));
    }

    public function update(Request $request, Outlet $outlet, Product $product): RedirectResponse
    {
        $user = $this->authorize($outlet, $product);

        if (!$user->hasPermission('product.edit')) {
            abort(403);
        }

        $data = $request->validate([
            'new_price' => ['required', 'integer', 'min:0'],
            'notes'     => ['nullable', 'string', 'max:255'],
        ]);

        if ((int) $data['new_price'] === $product->price) {
            return back()->with('info', 'Harga tidak berubah.');
        }

        $oldPrice = $product->price;
        $newPrice = (int) $data['new_price'];

        ProductPriceHistory::create([
            'product_id'      => $product->id,
            'outlet_id'       => $outlet->id,
            'changed_by'      => $user->id,
            'old_price'       => $oldPrice,
            'new_price'       => $newPrice,
            'stock_at_change' => $product->stock,
            'notes'           => $data['notes'] ?? null,
        ]);

        $product->update(['price' => $newPrice]);

        return back()->with('success', "Harga {$product->name} diperbarui: Rp " . number_format($oldPrice) . " → Rp " . number_format($newPrice) . ".");
    }
}
