<?php

namespace App\Services;

use App\Models\Outlet;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    private string $baseUrl;
    private string $serverKey;

    public function __construct(Outlet $outlet)
    {
        $this->serverKey = (string) $outlet->midtrans_server_key;
        $this->baseUrl   = $outlet->midtrans_is_production
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /** Buat tagihan QRIS dinamis. Mengembalikan ['success'=>bool, 'qr_url'?, 'message'?]. */
    public function chargeQris(string $orderId, int $grossAmount): array
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->post("{$this->baseUrl}/charge", [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $grossAmount,
                ],
            ]);

        $body = $response->json() ?? [];

        if (!$response->successful()) {
            return ['success' => false, 'message' => $body['status_message'] ?? 'Gagal menghubungi Midtrans.'];
        }

        $qrUrl = collect($body['actions'] ?? [])->firstWhere('name', 'generate-qr-code')['url'] ?? null;

        if (!$qrUrl) {
            return ['success' => false, 'message' => 'Midtrans tidak mengembalikan QR code.'];
        }

        return ['success' => true, 'qr_url' => $qrUrl];
    }

    /** Cek status transaksi Midtrans berdasarkan order_id. */
    public function checkStatus(string $orderId): array
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get("{$this->baseUrl}/{$orderId}/status");

        return $response->json() ?? [];
    }
}
