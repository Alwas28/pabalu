<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OwnerBillingController extends Controller
{
    public function index(): View
    {
        $user    = auth()->user();
        $invoice = BillingInvoice::activeFor($user->id);
        $history = BillingInvoice::where('user_id', $user->id)
            ->latest()->limit(10)->get();

        $midtransEnabled   = Setting::get('midtrans_enabled', '0') === '1';
        $midtransClientKey = $midtransEnabled ? Setting::get('midtrans_client_key') : null;
        $isProd            = Setting::get('midtrans_is_production', '0') === '1';
        $midtransSnapUrl   = $isProd
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        return view('billing.index', compact(
            'invoice', 'history', 'midtransEnabled', 'midtransClientKey', 'midtransSnapUrl'
        ));
    }

    public function markPaid(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user    = auth()->user();
        $orderId = $request->input('order_id');

        $invoice = BillingInvoice::where('user_id', $user->id)
            ->where('payment_ref', $orderId)
            ->where('status', 'unpaid')
            ->first();

        if (! $invoice) {
            return response()->json(['ok' => false, 'message' => 'Tagihan tidak ditemukan.'], 404);
        }

        $invoice->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'snap_token'  => null,
        ]);

        if ($user->account_type === 'inactive') {
            $user->update(['account_type' => 'premium', 'trial_ends_at' => null]);
        }

        \App\Models\ActivityLog::record('billing_paid',
            "Tagihan #{$invoice->id} (Rp " . number_format($invoice->amount, 0, ',', '.') . ") lunas via Snap.",
            $invoice
        );

        return response()->json(['ok' => true]);
    }

    public function snapToken(): JsonResponse
    {
        $user    = auth()->user();
        $invoice = BillingInvoice::activeFor($user->id);

        if (! $invoice) {
            return response()->json(['message' => 'Tidak ada tagihan aktif.'], 404);
        }

        if ($invoice->isSnapTokenValid()) {
            return response()->json([
                'snap_token' => $invoice->snap_token,
                'order_id'   => $invoice->payment_ref,
            ]);
        }

        $serverKey = Setting::get('midtrans_server_key', '');
        $isProd    = Setting::get('midtrans_is_production', '0') === '1';

        if (empty($serverKey) || Setting::get('midtrans_enabled', '0') !== '1') {
            return response()->json(['message' => 'Pembayaran online belum dikonfigurasi.'], 422);
        }

        $orderId = 'BILL-' . $invoice->id . '-' . date('YmdHis');

        try {
            \Midtrans\Config::$serverKey    = $serverKey;
            \Midtrans\Config::$isProduction = $isProd;
            \Midtrans\Config::$isSanitized  = true;
            \Midtrans\Config::$is3ds        = true;

            $rawMethods = array_filter(
                array_map('trim', explode(',', Setting::get('billing_payment_methods', '')))
            );

            // Midtrans Snap tidak mengenal 'qris' — harus dikirim sebagai 'gopay' dan 'other_qris'
            $enabledPayments = [];
            foreach ($rawMethods as $m) {
                if ($m === 'qris') {
                    $enabledPayments[] = 'gopay';
                    $enabledPayments[] = 'other_qris';
                } else {
                    $enabledPayments[] = $m;
                }
            }
            $enabledPayments = array_values(array_unique($enabledPayments));

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $invoice->amount,
                ],
                'item_details' => [[
                    'id'       => 'billing-' . $invoice->id,
                    'price'    => (int) $invoice->amount,
                    'quantity' => 1,
                    'name'     => mb_substr($invoice->description, 0, 50),
                ]],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                ],
                'custom_field1' => (string) $invoice->id,
                'custom_field2' => 'billing',
            ];

            if (! empty($enabledPayments)) {
                $params['enabled_payments'] = $enabledPayments;
            }

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $invoice->update([
                'snap_token'            => $snapToken,
                'snap_token_expires_at' => now()->addHours(24),
                'payment_ref'           => $orderId,
            ]);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat sesi pembayaran: ' . $e->getMessage()], 500);
        }
    }
}
