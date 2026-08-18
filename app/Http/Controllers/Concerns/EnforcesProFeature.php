<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Outlet;
use App\Models\User;

/**
 * Mengunci akses ke fitur yang terdaftar di katalog Paket Pro (`pro_features`).
 * Pemeriksaan berdasarkan paket AKTIF milik OWNER outlet (bukan user yang sedang
 * login) — kasir/admin_outlet bekerja atas nama paket ownernya. Admin sistem selalu
 * bebas, konsisten dengan enforceOutletTypeLimit()/enforceKasirLimit().
 */
trait EnforcesProFeature
{
    private function ownerHasProFeature(Outlet $outlet, User $actingUser, string $slug): bool
    {
        if ($actingUser->role === 'admin') {
            return true;
        }

        $owner = $outlet->loadMissing('owner')->owner;

        return (bool) $owner?->currentProPlan()->hasFeature($slug);
    }

    private function requireProFeature(Outlet $outlet, User $actingUser, string $slug, string $featureLabel): void
    {
        if ($this->ownerHasProFeature($outlet, $actingUser, $slug)) {
            return;
        }

        $planName = $outlet->owner?->currentProPlan()->name ?? 'Free';

        abort(403, "Fitur \"{$featureLabel}\" tidak termasuk di paket Anda saat ini ({$planName}). Upgrade paket untuk mengaktifkan fitur ini.");
    }
}
