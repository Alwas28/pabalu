<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Outlet;

/**
 * Batas JUMLAH akun kasir/admin_outlet PER OUTLET (bukan total di seluruh akun
 * owner) — dicek berdasarkan plan OWNER outlet itu sendiri (bukan user yang login,
 * supaya konsisten kalau suatu saat admin bertindak atas nama owner). $excludeUserId
 * dipakai saat update supaya user yang sedang diedit tidak ikut terhitung dobel
 * untuk outlet yang memang sudah jadi miliknya.
 */
trait EnforcesKasirLimit
{
    private function outletKasirLimitMessage(Outlet $outlet, ?int $excludeUserId = null): ?string
    {
        $plan = $outlet->owner?->currentProPlan();
        $maxKasir = $plan?->max_kasir;
        if ($maxKasir === null) {
            return null;
        }

        $count = $outlet->employees()
            ->when($excludeUserId, fn ($q) => $q->where('users.id', '!=', $excludeUserId))
            ->whereHas('roleRelation', fn ($r) => $r->whereNotIn('slug', ['admin', 'owner']))
            ->count();

        if ($count >= $maxKasir) {
            return "Outlet \"{$outlet->name}\" sudah mencapai batas maksimal {$maxKasir} akun kasir/admin outlet sesuai paket ({$plan->name}).";
        }

        return null;
    }
}
