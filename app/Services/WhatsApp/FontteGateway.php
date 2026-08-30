<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;

// API tidak resmi (unofficial) — Fonnte membungkus sesi WhatsApp Web nomor yang
// di-scan lewat dashboard mereka jadi REST API. Satu endpoint tetap untuk semua
// akun, identitas pengirim ditentukan oleh API key (bukan parameter terpisah).
// Dok: https://docs.fonnte.com/
class FontteGateway implements WhatsAppGatewayContract
{
    private const ENDPOINT = 'https://api.fonnte.com/send';

    public function __construct(private readonly string $apiKey)
    {
    }

    public function send(string $phoneNumber, string $message): array
    {
        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $this->apiKey])
                ->timeout(15)
                ->post(self::ENDPOINT, [
                    'target'  => $phoneNumber,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Tidak bisa terhubung ke Fonnte: ' . $e->getMessage()];
        }

        $body = $response->json() ?? [];

        if ($response->successful() && ($body['status'] ?? false) === true) {
            return ['success' => true, 'message' => 'Pesan berhasil dikirim lewat Fonnte.'];
        }

        $reason = $body['reason'] ?? $body['detail'] ?? ('HTTP ' . $response->status());

        return ['success' => false, 'message' => "Fonnte menolak pengiriman: {$reason}"];
    }
}
