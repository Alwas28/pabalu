<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Setiap baris = satu periode langganan. Baris dengan activated_at TERBARU milik
// seorang owner adalah langganan yang sedang berlaku — riwayat lama tetap tersimpan
// (tidak di-update/overwrite), jadi tabel ini otomatis jadi riwayat kode juga.
#[Fillable(['owner_id', 'pro_plan_id', 'pro_redemption_code_id', 'activated_at', 'expires_at', 'reminder_sent_at'])]
class ProOwnerSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'activated_at'     => 'datetime',
            'expires_at'       => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProPlan::class, 'pro_plan_id');
    }

    public function redemptionCode(): BelongsTo
    {
        return $this->belongsTo(ProRedemptionCode::class, 'pro_redemption_code_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function daysLeft(): ?int
    {
        return $this->expires_at ? now()->diffInDays($this->expires_at, false) : null;
    }

    // Baris langganan yang sedang berlaku untuk satu owner (activated_at paling akhir).
    public static function currentFor(int $ownerId): ?self
    {
        return static::where('owner_id', $ownerId)
            ->orderByDesc('activated_at')
            ->first();
    }
}
