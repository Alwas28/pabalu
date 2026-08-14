<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
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

    // Kategori kini dikelola admin per jenis outlet (lihat OutletTypeCategoryController) —
    // owner/kasir hanya bisa melihat, tidak bisa tambah/ubah/hapus kategori sendiri lagi.
    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $categories = $outlet->categories()->get();

        return view('laundry.categories.index', compact('outlet', 'user', 'categories'));
    }
}
