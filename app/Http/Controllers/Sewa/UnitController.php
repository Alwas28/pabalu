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

class UnitController extends Controller
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

    private function rules(Request $request, Outlet $outlet, ?RentalUnit $unit = null): array
    {
        return [
            'rental_item_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('rental_items', 'id')->where('outlet_id', $outlet->id),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('rental_units', 'code')
                    ->where('rental_item_id', $request->input('rental_item_id'))
                    ->ignore($unit?->id),
            ],
            'condition' => ['nullable', 'string', 'max:100'],
            'status'    => ['required', 'in:' . implode(',', array_keys(RentalUnit::STATUSES))],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $items = $outlet->rentalItems()->with(['units' => fn ($q) => $q->orderBy('code')])->get();
        $units = $items->flatMap(fn ($item) => $item->units->map(function ($unit) use ($item) {
            $unit->setRelation('rentalItem', $item);
            return $unit;
        }))->sortBy([['rentalItem.name', 'asc'], ['code', 'asc']])->values();

        return view('sewa.units.index', compact('outlet', 'user', 'items', 'units'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $data = $request->validate($this->rules($request, $outlet));

        RentalUnit::create($data);

        return back()->with('success', 'Unit berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet, RentalUnit $unit): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeUnit($outlet, $unit);

        $data = $request->validate($this->rules($request, $outlet, $unit));

        $unit->update($data);

        return back()->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet, RentalUnit $unit): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeUnit($outlet, $unit);

        $unit->delete();

        return back()->with('success', 'Unit berhasil dihapus.');
    }
}
