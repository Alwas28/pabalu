<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
