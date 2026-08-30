<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;

// Provider self-host — bukan API pihak ketiga, tapi service Node.js (Baileys)
// milik sendiri yang jalan berdampingan dengan Laravel (lihat folder
// /whatsapp-gateway di root repo). $baseUrl menunjuk ke alamat service itu
// (default http://127.0.0.1:3001), $apiKey adalah secret yang HARUS sama persis
// dengan API_KEY di file .env service tersebut.
class SelfHostedGateway implements WhatsAppGatewayContract
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
    ) {
    }

    public function send(string $phoneNumber, string $message): array
    {
        $baseUrl = rtrim($this->baseUrl, '/');

        try {
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(15)
                ->post("{$baseUrl}/send", [
                    'phone'   => $phoneNumber,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Tidak bisa terhubung ke service WhatsApp self-host: ' . $e->getMessage()];
        }

        $body = $response->json() ?? [];

        if ($response->successful() && ($body['success'] ?? false) === true) {
            return ['success' => true, 'message' => 'Pesan berhasil dikirim lewat service self-host.'];
        }

        $reason = $body['error'] ?? ('HTTP ' . $response->status());

        return ['success' => false, 'message' => "Service self-host menolak pengiriman: {$reason}"];
    }
}
