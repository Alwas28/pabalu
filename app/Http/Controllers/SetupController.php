<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role !== 'owner') {
            return redirect()->route('dashboard');
        }
        if ($user->setup_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('setup.index', ['user' => $user]);
    }

    public function saveProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
        ]);

        session(['setup_business_name' => $request->business_name]);

        return redirect()->route('setup.outlet');
    }

    public function outlet(): View|RedirectResponse
    {
        if (!session('setup_business_name')) {
            return redirect()->route('setup.index');
        }

        $user        = Auth::user();
        $outletTypes = OutletType::where('is_active', true)->where('slug', '!=', 'lainnya')->orderBy('sort_order')->get();

        return view('setup.outlet', compact('user', 'outletTypes'));
    }

    public function saveOutlet(Request $request): RedirectResponse
    {
        if (!session('setup_business_name')) {
            return redirect()->route('setup.index');
        }

        $request->validate([
            'outlet_name'    => ['required', 'string', 'max:150'],
            'outlet_type_id' => ['required', 'exists:outlet_types,id'],
            'outlet_address' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'business_name'      => session('setup_business_name'),
            'setup_completed_at' => now(),
        ]);

        Outlet::create([
            'owner_id'       => $user->id,
            'outlet_type_id' => $request->outlet_type_id,
            'name'           => $request->outlet_name,
            'address'        => $request->outlet_address,
            'code'           => $this->generateOutletCode(),
        ]);

        session()->forget('setup_business_name');
        session(['pabalu_welcome_tour' => true]);

        return redirect()->route('setup.done');
    }

    public function done(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->setup_completed_at) {
            return redirect()->route('setup.index');
        }

        $outlet = $user->outlets()->with('outletType')->latest()->first();

        return view('setup.done', compact('user', 'outlet'));
    }

    private function generateOutletCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }
}
