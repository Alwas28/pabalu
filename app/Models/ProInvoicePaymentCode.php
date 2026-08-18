<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'max_uses', 'max_uses_per_outlet', 'uses_count', 'is_active', 'expires_at', 'is_free', 'created_by'])]
class ProInvoicePaymentCode extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'is_free'    => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ProInvoicePaymentUsage::class);
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

    // Berapa kali outlet TERTENTU sudah memakai kode ini — dipakai untuk menegakkan
    // max_uses_per_outlet, terpisah dari max_uses yang membatasi total gabungan semua outlet.
    public function usesByOutlet(int $outletId): int
    {
        $usages = $this->relationLoaded('usages') ? $this->usages : $this->usages()->get();

        return $usages->where('outlet_id', $outletId)->count();
    }

    // Sama seperti isRedeemable(), tapi juga menegakkan batas pemakaian per-outlet
    // (kalau diset). Dipakai saat outlet melunasi tagihan lewat kode.
    public function isRedeemableByOutlet(int $outletId): bool
    {
        if (!$this->isRedeemable()) {
            return false;
        }
        if ($this->max_uses_per_outlet !== null && $this->usesByOutlet($outletId) >= $this->max_uses_per_outlet) {
            return false;
        }

        return true;
    }

    // Status untuk tampilan admin. Pakai uses_count yang sudah tersimpan di kolom ini
    // (bukan hitung dari relasi) supaya konsisten dengan isRedeemable().
    public function getStatusAttribute(): string
    {
        if ($this->uses_count >= $this->max_uses) {
            return 'terpakai';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'kadaluarsa';
        }

        return $this->is_active ? 'aktif' : 'nonaktif';
    }

    // Daftar outlet yang sudah memakai kode ini (bisa lebih dari 1 kalau max_uses > 1).
    // Panggil dengan usages.outlet.owner sudah di-eager-load supaya tidak N+1.
    public function getUsedByListAttribute(): \Illuminate\Support\Collection
    {
        $usages = $this->relationLoaded('usages') ? $this->usages : $this->usages()->with('outlet.owner')->get();

        return $usages->map(fn ($u) => [
            'outlet_name' => $u->outlet?->name ?? '(outlet terhapus)',
            'owner_name'  => $u->outlet?->owner?->name,
            'at'          => $u->used_at,
        ]);
    }
}
