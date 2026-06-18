<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Outlet;
use Illuminate\Support\Carbon;

/**
 * Menentukan "tanggal bisnis" aktif untuk outlet F&B yang bisa buka melewati tengah malam.
 *
 * Logika:
 *  - Jika outlet tidak wajib opening stok → pakai tanggal kalender (today()).
 *  - Jika ada opening stok yang belum di-closing → tanggal bisnis = tanggal opening tersebut.
 *  - Jika opening terakhir sudah di-closing → tanggal bisnis = today() (kalender),
 *    dan POS harus menunggu opening stok berikutnya.
 */
trait ResolvesBusinessDate
{
    /**
     * Tanggal bisnis aktif (string Y-m-d).
     * Untuk outlet non-opening: today(). Untuk outlet F&B: tanggal opening yang masih terbuka.
     */
    protected function getBusinessDate(Outlet $outlet): string
    {
        return $this->resolveBusinessDay($outlet)['date'];
    }

    /**
     * Apakah sesi bisnis hari ini sudah "terbuka" (opening stok dilakukan, belum closing).
     */
    protected function isBusinessDayOpen(Outlet $outlet): bool
    {
        return $this->resolveBusinessDay($outlet)['open'];
    }

    /**
     * Resolve sekali, hasilkan ['date' => 'Y-m-d', 'open' => bool].
     */
    private function resolveBusinessDay(Outlet $outlet): array
    {
        $requiresOpening = $outlet->outletType?->requires_opening_stock ?? false;

        if (!$requiresOpening) {
            return ['date' => today()->toDateString(), 'open' => true];
        }

        // Opening stok terbaru (belum tentu hari ini — bisa kemarin jika lewat tengah malam)
        $lastOpening = $outlet->stockOpnames()
            ->where('type', 'opening')
            ->orderByDesc('date')
            ->first();

        if (!$lastOpening) {
            // Belum pernah ada opening stok
            return ['date' => today()->toDateString(), 'open' => false];
        }

        $businessDate = $lastOpening->date->toDateString();

        // Cek apakah business date ini sudah di-closing
        $hasClosed = $outlet->dailyClosings()
            ->where('date', $businessDate)
            ->exists();

        if ($hasClosed) {
            // Hari bisnis sebelumnya sudah tutup → sesi baru, tunggu opening berikutnya
            return ['date' => today()->toDateString(), 'open' => false];
        }

        // Opening ada, belum closing → sesi masih terbuka
        return ['date' => $businessDate, 'open' => true];
    }
}
