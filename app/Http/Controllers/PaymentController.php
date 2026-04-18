<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OwnerSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    /**
     * Midtrans notification/webhook callback.
     * Route: POST /payment/callback  (CSRF excluded)
     */
    public function callback(Request $request): Response|JsonResponse
    {
        $orderId     = $request->input('order_id');

        // Resolve server key dari owner outlet order
        $order = Order::where('order_number', $orderId)->with('outlet')->first();
        $serverKey = $order
            ? OwnerSetting::get('midtrans_server_key', $order->outlet->owner_id, '')
            : '';
        $statusCode  = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');

        // Verifikasi signature
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($expected !== $request->input('signature_key')) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $txStatus    = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status', 'accept');

        if ($txStatus === 'capture' && $fraudStatus === 'accept') {
            // Kartu kredit — capture + fraud accept → bayar
            $order->update(['order_status' => 'pending', 'paid_at' => now()]);
        } elseif ($txStatus === 'settlement') {
            // Transfer bank / e-wallet / QRIS — settled → bayar
            $order->update(['order_status' => 'pending', 'paid_at' => now()]);
        } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['order_status' => 'cancelled']);
        }
        // pending / challenge → tidak ubah status, tunggu settlement

        return response()->noContent();
    }
}
