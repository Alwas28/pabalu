<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

// Throttle yang batasnya bisa diatur admin lewat Pengaturan Sistem > Rate Limiting
// (disimpan di AppSetting), bukan angka mati di kode seperti `throttle:10,1` bawaan
// Laravel. Dipakai sebagai middleware route (`dynamic.throttle:login`) MAUPUN
// dipanggil langsung lewat resolveLimit() dari tempat lain yang perlu tahu batasnya
// tanpa jadi middleware (mis. LoginRequest::ensureIsNotRateLimited()).
class DynamicThrottle
{
    // Dipakai kalau admin belum pernah mengatur profil ini — juga ditampilkan
    // sebagai placeholder di form pengaturan.
    public const DEFAULTS = [
        'login'         => ['max' => 5,  'minutes' => 1],
        'register'      => ['max' => 10, 'minutes' => 1],
        'otp'           => ['max' => 6,  'minutes' => 1],
        'public_lookup' => ['max' => 20, 'minutes' => 1],
    ];

    public const LABELS = [
        'login'         => 'Login (web & API)',
        'register'      => 'Registrasi Akun Baru',
        'otp'           => 'Verifikasi / Kirim Ulang OTP',
        'public_lookup' => 'Lacak Pesanan Publik (tanpa login)',
    ];

    public function handle(Request $request, Closure $next, string $name): Response
    {
        [$max, $minutes] = self::resolveLimit($name);

        $key = "dynamic_throttle:{$name}:" . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);
            $message = "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 429, ['Retry-After' => $seconds]);
            }

            abort(429, $message);
        }

        RateLimiter::hit($key, $minutes * 60);

        return $next($request);
    }

    /**
     * @return array{0: int, 1: int} [maxAttempts, windowMinutes]
     */
    public static function resolveLimit(string $name): array
    {
        $defaults = self::DEFAULTS[$name] ?? ['max' => 10, 'minutes' => 1];

        $max     = (int) (AppSetting::get("rate_limit_{$name}_max") ?? $defaults['max']);
        $minutes = (int) (AppSetting::get("rate_limit_{$name}_minutes") ?? $defaults['minutes']);

        // Jangan biarkan admin salah isi 0/negatif sampai efektif mematikan limit sepenuhnya.
        return [max($max, 1), max($minutes, 1)];
    }
}
