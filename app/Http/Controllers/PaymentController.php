<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BillingInvoice;
use App\Models\Order;
use App\Models\OwnerSetting;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Midtrans notification/webhook callback.
     * Route: POST /payment/callback  (CSRF excluded)
     */
    public function callback(Request $request): Response|JsonResponse
    {
        $orderId     = $request->input('order_id');
        $statusCode  = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $txStatus    = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status', 'accept');

        // Route berdasarkan prefix order_id
        if (str_starts_with($orderId, 'POS-')) {
            return $this->handlePosCallback($request, $orderId, $statusCode, $grossAmount, $txStatus, $fraudStatus);
        }

        if (str_starts_with($orderId, 'BILL-')) {
            return $this->handleBillingCallback($request, $orderId, $statusCode, $grossAmount, $txStatus, $fraudStatus);
        }

        // ── Public Order ─────────────────────────────────────
        $order     = Order::where('order_number', $orderId)->with('outlet')->first();
        $serverKey = $order
            ? OwnerSetting::get('midtrans_server_key', $order->outlet->owner_id, '')
            : '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($expected !== $request->input('signature_key')) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($txStatus === 'capture' && $fraudStatus === 'accept') {
            $order->update(['order_status' => 'pending', 'paid_at' => now()]);
        } elseif ($txStatus === 'settlement') {
            $order->update(['order_status' => 'pending', 'paid_at' => now()]);
        } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['order_status' => 'cancelled']);
        }

        return response()->noContent();
    }

    private function handlePosCallback(
        Request $request,
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $txStatus,
        string $fraudStatus
    ): Response|JsonResponse {
        // Cek apakah transaksi sudah tersimpan sebelumnya (dari onSuccess)
        $exists = Transaction::where('payment_ref', $orderId)->exists();
        if ($exists) {
            return response()->noContent(); // sudah tercatat, abaikan
        }

        // Hanya proses jika settled/captured
        $settled = ($txStatus === 'settlement')
            || ($txStatus === 'capture' && $fraudStatus === 'accept');

        if (! $settled) {
            return response()->noContent();
        }

        // Ambil detail transaksi dari Midtrans untuk verifikasi & data item
        // Cari outlet dari custom_field atau ambil dari metadata Midtrans
        $outletId = $request->input('custom_field1');
        $kasirId  = $request->input('custom_field2');
        $itemsJson = $request->input('custom_field3');

        if (! $outletId || ! $itemsJson) {
            // Tidak ada data cukup untuk rekonstruksi — catat di log saja
            \Log::warning("POS webhook: missing custom_fields for order {$orderId}");
            return response()->noContent();
        }

        $items   = json_decode($itemsJson, true);
        $total   = (int) $grossAmount;
        $tanggal = today()->toDateString();

        DB::transaction(function () use ($outletId, $kasirId, $orderId, $total, $tanggal, $items) {
            $nomor = Transaction::generateNomor((int) $outletId, $tanggal);

            $trx = Transaction::create([
                'outlet_id'       => $outletId,
                'kasir_id'        => $kasirId,
                'nomor_transaksi' => $nomor,
                'tanggal'         => $tanggal,
                'total'           => $total,
                'bayar'           => $total,
                'kembalian'       => 0,
                'status'          => 'paid',
                'metode_bayar'    => 'gateway',
                'payment_ref'     => $orderId,
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['product_id'],
                    'nama_produk'    => $item['nama'],
                    'harga_satuan'   => $item['harga'],
                    'qty'            => $item['qty'],
                    'subtotal'       => $item['subtotal'],
                ]);
            }

            ActivityLog::record('create_transaction',
                "Transaksi {$nomor} dibuat via webhook gateway (POS). Total: Rp " . number_format($total, 0, ',', '.'),
                $trx
            );
        });

        return response()->noContent();
    }

    private function handleBillingCallback(
        Request $request,
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $txStatus,
        string $fraudStatus
    ): Response|JsonResponse {
        $serverKey = Setting::get('midtrans_server_key', '');
        $expected  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($expected !== $request->input('signature_key')) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $invoiceId = $request->input('custom_field1');
        $invoice   = BillingInvoice::with('owner')->find($invoiceId);

        if (! $invoice || $invoice->status !== 'unpaid') {
            return response()->noContent();
        }

        if (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
            $invoice->update(['snap_token' => null, 'snap_token_expires_at' => null]);
            return response()->noContent();
        }

        $settled = ($txStatus === 'settlement')
            || ($txStatus === 'capture' && $fraudStatus === 'accept');

        if (! $settled) {
            return response()->noContent();
        }

        $invoice->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'payment_ref' => $orderId,
        ]);

        // Reaktivasi akun jika disuspend karena billing
        $owner = $invoice->owner;
        if ($owner && $owner->account_type === 'inactive') {
            $owner->update(['account_type' => 'premium', 'trial_ends_at' => null]);
        }

        ActivityLog::record('billing_paid',
            "Tagihan #{$invoice->id} (Rp " . number_format($invoice->amount, 0, ',', '.') . ") lunas via payment gateway.",
            $invoice
        );

        return response()->noContent();
    }
}
