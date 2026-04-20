<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $outlets = $request->user()
            ->accessibleOutlets()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'telepon']);

        return response()->json($outlets);
    }
}
