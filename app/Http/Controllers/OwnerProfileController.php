<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerProfileController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $auth */
        $auth = Auth::user();
        abort_unless($auth->role === 'admin', 403);

        $owners = User::whereHas('roleRelation', fn($q) => $q->where('slug', 'owner'))
            ->withCount('outlets')
            ->with('currentProSubscription.plan')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->q . '%';
                $query->where(fn($sq) => $sq
                    ->where('name', 'like', $q)
                    ->orWhere('email', 'like', $q)
                    ->orWhere('business_name', 'like', $q)
                );
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.owners', compact('owners'));
    }

    public function show(User $user): View
    {
        /** @var User $auth */
        $auth = Auth::user();
        abort_unless($auth->role === 'admin', 403);
        abort_unless($user->role === 'owner', 404);

        $user->load(['outlets.outletType', 'outlets.regency', 'outlets.province']);

        return view('admin.owner-profile', compact('user'));
    }

    // Kuota outlet tambahan khusus owner ini, di luar batas paket Pro-nya — dipakai
    // admin waktu owner butuh lebih dari batas paket tapi belum perlu/mau upgrade paket.
    public function updateOutletQuota(Request $request, User $user): RedirectResponse
    {
        /** @var User $auth */
        $auth = Auth::user();
        abort_unless($auth->role === 'admin', 403);
        abort_unless($user->role === 'owner', 404);

        $data = $request->validate([
            'extra_outlet_quota' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $user->update(['extra_outlet_quota' => $data['extra_outlet_quota']]);

        return back()->with('success', "Kuota outlet tambahan untuk \"{$user->name}\" berhasil diperbarui.");
    }
}
