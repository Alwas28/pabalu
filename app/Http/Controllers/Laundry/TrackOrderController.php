<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function show(string $orderNumber): View
    {
        $order = LaundryOrder::where('order_number', $orderNumber)
            ->with(['items', 'outlet'])
            ->firstOrFail();

        return view('laundry.track', compact('order'));
    }
}
