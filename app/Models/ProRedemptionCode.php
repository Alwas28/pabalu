<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'pro_plan_id', 'valid_days', 'max_uses', 'uses_count', 'expires_at', 'is_active', 'created_by'])]
class ProRedemptionCode extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active'  => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProPlan::class, 'pro_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProOwnerSubscription::class);
    }

    public function isRedeemable(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->uses_count < $this->max_uses;
    }

    // Status untuk tampilan admin. Pakai subscriptions yang sudah di-eager-load
    // (relationLoaded) supaya tidak N+1 saat dipanggil dari daftar kode.
    public function getStatusAttribute(): string
    {
        if ($this->uses_count >= $this->max_uses) {
            return 'terpakai';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'kadaluarsa';
        }

        return 'aktif';
    }

    // Daftar owner yang sudah memakai kode ini (bisa lebih dari 1 kalau max_uses > 1).
    // Panggil dengan subscriptions.owner sudah di-eager-load supaya tidak N+1.
    public function getUsedByListAttribute(): \Illuminate\Support\Collection
    {
        $subs = $this->relationLoaded('subscriptions')
            ? $this->subscriptions
            : $this->subscriptions()->with('owner')->get();

        return $subs->map(fn ($s) => ['name' => $s->owner?->name, 'at' => $s->activated_at]);
    }
}
