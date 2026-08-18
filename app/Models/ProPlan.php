<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'price', 'max_outlet_types', 'max_kasir', 'is_active', 'is_default', 'is_self_activatable', 'sort_order'])]
class ProPlan extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'            => 'boolean',
            'is_default'           => 'boolean',
            'is_self_activatable'  => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(ProFeature::class, 'pro_plan_feature', 'pro_plan_id', 'pro_feature_id');
    }

    public function redemptionCodes(): HasMany
    {
        return $this->hasMany(ProRedemptionCode::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProOwnerSubscription::class);
    }

    public function allowedOutletTypes(): BelongsToMany
    {
        return $this->belongsToMany(OutletType::class, 'pro_plan_outlet_type');
    }

    public function hasFeature(string $slug): bool
    {
        return $this->relationLoaded('features')
            ? $this->features->contains('slug', $slug)
            : $this->features()->where('slug', $slug)->exists();
    }

    // Tidak ada baris di allowedOutletTypes = tidak dibatasi (semua jenis boleh) — supaya
    // paket lama yang belum pernah diatur admin tetap tidak terpengaruh.
    public function allowsOutletType(int $outletTypeId): bool
    {
        $types = $this->relationLoaded('allowedOutletTypes') ? $this->allowedOutletTypes : $this->allowedOutletTypes()->get();

        return $types->isEmpty() || $types->contains('id', $outletTypeId);
    }
}
