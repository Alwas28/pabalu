<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    // Daftar outlet yang boleh diakses user login — sama persis dengan logika web OutletController::index().
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $with = ['outletType'];
        $outlets = match ($user->role) {
            'admin'  => Outlet::with($with)->where('is_active', true)->latest()->get(),
            'owner'  => $user->outlets()->with($with)->where('is_active', true)->latest()->get(),
            default  => $user->assignedOutlets()->with($with)->where('is_active', true)->get(),
        };

        return response()->json(['data' => OutletResource::collection($outlets)]);
    }
}
