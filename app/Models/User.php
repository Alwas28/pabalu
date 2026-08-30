<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'is_active', 'phone', 'business_name', 'setup_completed_at', 'extra_outlet_quota', 'created_by'])]
#[Hidden(['password', 'remember_token', 'wa_verification_code'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // Always eager-load role so $user->role is zero extra queries
    protected $with = ['roleRelation'];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'setup_completed_at'         => 'datetime',
            'wa_verification_expires_at' => 'datetime',
        ];
    }

    // Ada kode OTP WhatsApp yang belum dipakai & belum kedaluwarsa — dipakai halaman
    // prompt verifikasi buat memutuskan tampilkan form isi-kode atau tidak.
    public function hasPendingWaVerification(): bool
    {
        return $this->wa_verification_code !== null
            && $this->wa_verification_expires_at !== null
            && $this->wa_verification_expires_at->isFuture();
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'owner_id');
    }

    public function assignedOutlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_employees');
    }

    // Riwayat penuh langganan Paket Pro (satu baris per periode aktivasi).
    public function proSubscriptions(): HasMany
    {
        return $this->hasMany(ProOwnerSubscription::class, 'owner_id');
    }

    // Baris langganan yang sedang berlaku (activated_at paling akhir DAN belum
    // kadaluarsa). Owner tanpa baris sama sekali, atau baris terakhirnya sudah
    // lewat expires_at, dianggap paket Free bawaan — tangani null di tampilan.
    public function currentProSubscription(): HasOne
    {
        return $this->hasOne(ProOwnerSubscription::class, 'owner_id')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latestOfMany('activated_at');
    }

    // Paket Pro yang sedang berlaku, fallback ke paket Free bawaan kalau belum
    // pernah redeem kode sama sekali.
    public function currentProPlan(): ProPlan
    {
        return $this->currentProSubscription?->plan ?? ProPlan::where('is_default', true)->firstOrFail();
    }

    public function hasCompletedSetup(): bool
    {
        return $this->role !== 'owner' || !is_null($this->setup_completed_at);
    }

    // Batas jumlah outlet efektif = batas paket + kuota tambahan khusus owner ini
    // (extra_outlet_quota, diatur admin per-owner). null = tanpa batas (paket sudah
    // tanpa batas, kuota tambahan jadi tidak relevan).
    public function effectiveOutletLimit(): ?int
    {
        $planLimit = $this->currentProPlan()->max_outlet_types;

        return $planLimit === null ? null : $planLimit + $this->extra_outlet_quota;
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Accessor: $user->role returns the slug string (e.g. 'admin')
    public function getRoleAttribute(): ?string
    {
        return $this->roleRelation?->slug;
    }

    // Mutator: $user->role = 'admin' resolves to role_id automatically
    public function setRoleAttribute(?string $slug): void
    {
        $this->attributes['role_id'] = $slug
            ? DB::table('roles')->where('slug', $slug)->value('id')
            : null;

        // Reset cached relation so subsequent ->role reads fresh slug
        unset($this->relations['roleRelation']);
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->roleRelation?->name ?? 'Pengguna';
    }

    public function hasPermission(string $slug): bool
    {
        if (!$this->role_id) return false;
        if ($this->role === 'admin') return true;

        $perms = Cache::remember(
            "role.{$this->role_id}.perms",
            now()->addMinutes(60),
            fn() => $this->roleRelation
                ?->permissions
                ->pluck('slug')
                ->toArray() ?? []
        );

        return in_array($slug, $perms);
    }
}
