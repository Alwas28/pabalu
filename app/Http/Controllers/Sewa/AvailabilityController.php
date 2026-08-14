<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvailabilityController extends Controller
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

    private function authorizeUnit(Outlet $outlet, RentalUnit $unit): void
    {
        if ((int) $unit->rentalItem->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $items = $outlet->rentalItems()
            ->with(['units' => fn ($q) => $q->orderBy('code'), 'images'])
            ->get();

        return view('sewa.availability.index', compact('outlet', 'user', 'items'));
    }

    public function updateStatus(Request $request, Outlet $outlet, RentalUnit $unit): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeUnit($outlet, $unit);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(RentalUnit::STATUSES))],
        ]);

        $unit->update($data);

        return back()->with('success', 'Status unit berhasil diperbarui.');
    }
}
