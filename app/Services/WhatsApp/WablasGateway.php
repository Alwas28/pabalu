<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;

// API tidak resmi (unofficial) — beda dari Fonnte, Wablas memberi domain server
// SENDIRI per akun saat daftar (mis. https://sby.wablas.com) — bukan satu endpoint
// tetap untuk semua orang, jadi $domain wajib diisi admin di pengaturan.
// Dok: https://wablas.com/documentation
class WablasGateway implements WhatsAppGatewayContract
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $domain,
    ) {
    }

    public function send(string $phoneNumber, string $message): array
    {
        $baseUrl = rtrim($this->domain, '/');

        try {
            $response = Http::withHeaders(['Authorization' => $this->apiKey])
                ->timeout(15)
                ->post("{$baseUrl}/api/send-message", [
                    'phone'   => $phoneNumber,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Tidak bisa terhubung ke Wablas: ' . $e->getMessage()];
        }

        $body = $response->json() ?? [];

        if ($response->successful() && ($body['status'] ?? false) === true) {
            return ['success' => true, 'message' => 'Pesan berhasil dikirim lewat Wablas.'];
        }

        $reason = $body['message'] ?? ('HTTP ' . $response->status());

        return ['success' => false, 'message' => "Wablas menolak pengiriman: {$reason}"];
    }
}
