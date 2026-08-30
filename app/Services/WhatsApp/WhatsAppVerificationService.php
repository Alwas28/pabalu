<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Verifikasi email lewat kode OTP dikirim WhatsApp — jalur ALTERNATIF dari link
// bertanda-tangan bawaan Laravel (VerifyEmailController), tapi tujuan akhirnya SAMA:
// menandai email_verified_at lewat User::markEmailAsVerified(). Dipakai dari
// RegisteredUserController (kirim kode pertama kali) dan WhatsAppVerificationController
// (verifikasi kode + kirim ulang).
class WhatsAppVerificationService
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    // Coba kirim kode OTP ke nomor WA user. false kalau gateway belum dikonfigurasi/
    // nonaktif, user tidak punya nomor, atau pengiriman ke provider gagal — pemanggil
    // (RegisteredUserController) HARUS fallback ke verifikasi email kalau ini false,
    // supaya user tidak pernah terjebak tanpa cara verifikasi sama sekali.
    public function sendCode(User $user): bool
    {
        if (!WhatsAppGatewayManager::isConfigured() || !$user->phone) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'wa_verification_code'       => Hash::make($code),
            'wa_verification_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'wa_verification_attempts'   => 0,
        ])->save();

        $message = "Kode verifikasi Pabalu Anda: *{$code}*\n\n"
            . 'Berlaku ' . self::CODE_TTL_MINUTES . ' menit. Jangan bagikan kode ini ke siapa pun, termasuk pihak yang mengaku dari Pabalu.';

        $result = WhatsAppGatewayManager::send($user->phone, $message);

        if (!$result['success']) {
            // Jangan biarkan kode "nyangkut" kalau pengiriman gagal — pemanggil akan
            // fallback ke email, dan percobaan kirim ulang WA berikutnya harus mulai bersih.
            $this->clearCode($user);
        }

        return $result['success'];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function verifyCode(User $user, string $code): array
    {
        if (!$user->wa_verification_code || !$user->wa_verification_expires_at) {
            return ['success' => false, 'message' => 'Tidak ada kode verifikasi aktif. Minta kode baru.'];
        }

        if ($user->wa_verification_expires_at->isPast()) {
            $this->clearCode($user);

            return ['success' => false, 'message' => 'Kode sudah kedaluwarsa. Minta kode baru.'];
        }

        if ($user->wa_verification_attempts >= self::MAX_ATTEMPTS) {
            $this->clearCode($user);

            return ['success' => false, 'message' => 'Terlalu banyak percobaan salah. Minta kode baru.'];
        }

        if (!Hash::check($code, $user->wa_verification_code)) {
            $user->increment('wa_verification_attempts');

            return ['success' => false, 'message' => 'Kode salah. Coba lagi.'];
        }

        $this->clearCode($user);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return ['success' => true, 'message' => 'Verifikasi berhasil.'];
    }

    private function clearCode(User $user): void
    {
        $user->forceFill([
            'wa_verification_code'       => null,
            'wa_verification_expires_at' => null,
            'wa_verification_attempts'   => 0,
        ])->save();
    }
}
