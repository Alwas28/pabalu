<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Katalog fitur yang bisa dikunci per Paket Pro — daftar ini dikelola developer lewat
// migrasi/seeder (ProFeatureSeeder), BUKAN admin lewat UI. Yang admin atur adalah
// fitur mana masuk ke paket mana (lihat ProPlan::features()).
#[Fillable(['slug', 'outlet_type', 'name', 'description', 'sort_order'])]
class ProFeature extends Model
{
    // Kategori jenis outlet yang dikenal sistem Pro + label tampilan.
    public const OUTLET_TYPES = [
        'kafe'         => 'F&B — Coffee & Resto',
        'warung_makan' => 'Rumah Makan / Warung Makan',
        'retail'       => 'Retail',
        'salon'        => 'Salon',
        'laundry'      => 'Laundry',
        'sewa'         => 'Sewa/Rental',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(ProPlan::class, 'pro_plan_feature', 'pro_feature_id', 'pro_plan_id');
    }

    // Memetakan slug OutletType (tabel outlet_types) ke kategori "jenis outlet" Pro.
    // kafe & warung_makan dipetakan langsung (1:1); kelontong/pakaian/elektronik/retail
    // semua masuk "retail"; jasa_sewa -> "sewa"; buku (fnb tapi bukan resto/kafe
    // sungguhan, belum punya sistem sendiri) sementara dibuatkan "warung_makan".
    public static function categoryForOutletTypeSlug(string $outletTypeSlug): string
    {
        return match ($outletTypeSlug) {
            'kafe'                              => 'kafe',
            'warung_makan', 'buku'              => 'warung_makan',
            'kelontong', 'pakaian', 'elektronik', 'retail' => 'retail',
            'salon'                             => 'salon',
            'laundry'                           => 'laundry',
            'jasa_sewa'                         => 'sewa',
            default                             => 'retail',
        };
    }
}
