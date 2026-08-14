<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function show(string $token): View
    {
        $order = LaundryOrder::where('tracking_token', $token)
            ->with(['items', 'outlet'])
            ->firstOrFail();

        return view('laundry.track', compact('order'));
    }
}
