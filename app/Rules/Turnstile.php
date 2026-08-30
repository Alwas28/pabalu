<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Verifikasi token widget Cloudflare Turnstile (field hidden `cf-turnstile-response`
// yang otomatis diisi widget di sisi klien) lewat endpoint siteverify Cloudflare.
//
// PENTING cara pakai: field ini harus di-set 'required' HANYA kalau Turnstile aktif
// (site_key terisi, lihat helper Turnstile::rules() di bawah) — Laravel skip rule
// custom untuk field kosong/absen kecuali ada 'required', dan widget-nya sendiri cuma
// dirender di blade kalau site_key ada (lihat resources/views/auth/login.blade.php &
// register.blade.php). Kalau langsung pasang `new Turnstile()` tanpa 'required'
// bersyarat, rule ini TIDAK PERNAH jalan selama Turnstile belum dikonfigurasi (field
// memang tidak pernah terkirim) — itu belum tentu salah, tapi begitu Turnstile
// dikonfigurasi, field WAJIB ada supaya validasi captcha benar-benar menolak token kosong.
class Turnstile implements ValidationRule
{
    /**
     * Rule array siap pakai untuk field `cf-turnstile-response` — otomatis 'required'
     * kalau Turnstile aktif (site_key terisi), 'nullable' kalau belum dikonfigurasi
     * sama sekali (widget tidak dirender, field tidak akan pernah terkirim).
     */
    public static function rules(): array
    {
        return config('services.cloudflare.turnstile.site_key')
            ? ['required', new self()]
            : ['nullable'];
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.cloudflare.turnstile.secret_key');

        // Secret key belum diisi admin (lihat TURNSTILE_SECRET_KEY di .env) = anggap
        // captcha belum diaktifkan, JANGAN blokir semua orang login/daftar gara-gara
        // lupa konfigurasi (fail-open, bukan fail-closed).
        if (!$secretKey) {
            return;
        }

        if (!$value) {
            $fail('Verifikasi keamanan (captcha) belum diselesaikan. Coba lagi.');

            return;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                ['secret' => $secretKey, 'response' => $value]
            );

            if (!($response->json('success') ?? false)) {
                $fail('Verifikasi keamanan (captcha) gagal. Silakan coba lagi.');
            }
        } catch (\Throwable $e) {
            // Cloudflare down/network error — jangan kunci pengguna keluar total dari
            // login/registrasi cuma karena verifikasi captcha tidak bisa dihubungi.
            Log::warning('Turnstile verification unreachable: ' . $e->getMessage());
        }
    }
}
