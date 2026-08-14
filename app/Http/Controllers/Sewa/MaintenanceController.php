<?php

namespace App\Http\Controllers\Sewa;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\RentalUnit;
use App\Models\RentalUnitMaintenance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MaintenanceController extends Controller
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

    private function authorizeMaintenance(Outlet $outlet, RentalUnitMaintenance $maintenance): void
    {
        if ((int) $maintenance->rentalUnit->rentalItem->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }

    private function rules(Outlet $outlet): array
    {
        return [
            'rental_unit_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('rental_units', 'id')->whereIn(
                    'rental_item_id',
                    $outlet->rentalItems()->pluck('id')
                ),
            ],
            'reason'      => ['required', 'string', 'max:255'],
            'cost'        => ['nullable', 'integer', 'min:0'],
            'started_at'  => ['required', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function index(Outlet $outlet): View
    {
        $user = $this->authorizeOutlet($outlet);
        $outlet->load('outletType');

        $itemIds = $outlet->rentalItems()->pluck('id');
        $unitIds = RentalUnit::whereIn('rental_item_id', $itemIds)->pluck('id');

        $maintenances = RentalUnitMaintenance::whereIn('rental_unit_id', $unitIds)
            ->with('rentalUnit.rentalItem')
            ->orderByRaw('finished_at IS NOT NULL')
            ->orderByDesc('started_at')
            ->get();

        $units = RentalUnit::whereIn('rental_item_id', $itemIds)
            ->with('rentalItem')
            ->orderBy('code')
            ->get();

        return view('sewa.maintenance.index', compact('outlet', 'user', 'maintenances', 'units'));
    }

    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOutlet($outlet);

        $data = $request->validate($this->rules($outlet));

        $maintenance = RentalUnitMaintenance::create($data);
        $maintenance->rentalUnit->update(['status' => 'maintenance']);

        return back()->with('success', 'Riwayat maintenance berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet, RentalUnitMaintenance $maintenance): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeMaintenance($outlet, $maintenance);

        $data = $request->validate($this->rules($outlet));

        $wasOngoing = $maintenance->isOngoing();
        $maintenance->update($data);

        if ($wasOngoing && $maintenance->finished_at !== null) {
            $maintenance->rentalUnit->update(['status' => 'tersedia']);
        } elseif ($maintenance->finished_at === null) {
            $maintenance->rentalUnit->update(['status' => 'maintenance']);
        }

        return back()->with('success', 'Riwayat maintenance berhasil diperbarui.');
    }

    public function finish(Outlet $outlet, RentalUnitMaintenance $maintenance): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeMaintenance($outlet, $maintenance);

        $maintenance->update(['finished_at' => now()->toDateString()]);
        $maintenance->rentalUnit->update(['status' => 'tersedia']);

        return back()->with('success', 'Maintenance ditandai selesai. Status unit dikembalikan ke Tersedia.');
    }

    public function destroy(Outlet $outlet, RentalUnitMaintenance $maintenance): RedirectResponse
    {
        $this->authorizeOutlet($outlet);
        $this->authorizeMaintenance($outlet, $maintenance);

        $maintenance->delete();

        return back()->with('success', 'Riwayat maintenance berhasil dihapus.');
    }
}
