<?php

namespace App\Services\WhatsApp;

use App\Models\AppSetting;

// Titik masuk tunggal buat kirim WA dari mana pun di aplikasi — tempat lain TIDAK
// PERLU tahu provider mana yang aktif atau bagaimana kredensialnya disimpan, cukup
// panggil WhatsAppGatewayManager::send(). Menambah provider baru = tambah satu kelas
// implementasi + satu baris di PROVIDERS + satu baris di driver(), tidak menyentuh
// kode pemanggil di controller lain (mis. nanti alur kirim OTP verifikasi).
class WhatsAppGatewayManager
{
    public const PROVIDERS = [
        'fonnte'   => 'Fonnte',
        'wablas'   => 'Wablas',
        'selfhost' => 'Self-Host (Baileys)',
    ];

    // Provider yang butuh field "domain/URL" tambahan di pengaturan (Wablas: domain
    // server per akun, Self-Host: alamat service Node.js kita sendiri) — dipakai
    // validasi controller & toggle tampilan field di halaman pengaturan.
    public const PROVIDERS_NEEDING_DOMAIN = ['wablas', 'selfhost'];

    public static function isEnabled(): bool
    {
        return AppSetting::get('wa_gateway_enabled', '0') === '1';
    }

    public static function isConfigured(): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $provider = AppSetting::get('wa_gateway_provider');
        $apiKey   = AppSetting::get('wa_gateway_api_key');

        if (!$provider || !array_key_exists($provider, self::PROVIDERS) || !$apiKey) {
            return false;
        }

        if (in_array($provider, self::PROVIDERS_NEEDING_DOMAIN, true) && !AppSetting::get('wa_gateway_api_domain')) {
            return false;
        }

        return true;
    }

    private static function driver(): ?WhatsAppGatewayContract
    {
        if (!self::isConfigured()) {
            return null;
        }

        $provider = AppSetting::get('wa_gateway_provider');
        $apiKey   = AppSetting::get('wa_gateway_api_key');

        return match ($provider) {
            'fonnte'   => new FontteGateway($apiKey),
            'wablas'   => new WablasGateway($apiKey, AppSetting::get('wa_gateway_api_domain')),
            'selfhost' => new SelfHostedGateway($apiKey, AppSetting::get('wa_gateway_api_domain')),
            default    => null,
        };
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function send(string $phoneNumber, string $message): array
    {
        $driver = self::driver();

        if (!$driver) {
            return ['success' => false, 'message' => 'Gateway WhatsApp belum dikonfigurasi atau belum diaktifkan.'];
        }

        return $driver->send(self::normalizePhone($phoneNumber), $message);
    }

    // Nomor Indonesia bisa masuk dalam berbagai format (08xx, +62xx, 62xx, dengan
    // spasi/strip) — gateway WA butuh format 62xx tanpa simbol apa pun.
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (!str_starts_with($digits, '62')) {
            return '62' . $digits;
        }

        return $digits;
    }
}
