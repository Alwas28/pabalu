<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'outlet_id', 'account_type', 'trial_ends_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'trial_ends_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class)->withDefault();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function ownedOutlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'owner_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isKasir(): bool
    {
        return $this->hasRole('kasir');
    }

    public function isTrialExpired(): bool
    {
        return $this->account_type === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    public function trialDaysLeft(): int
    {
        if ($this->account_type !== 'trial' || !$this->trial_ends_at) return 0;
        return (int) max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /** True jika akun bisa digunakan (belum expired, belum dinonaktifkan) */
    public function isAccountActive(): bool
    {
        if ($this->account_type === 'inactive') return false;
        if ($this->isTrialExpired()) return false;
        return true;
    }

    /** Ambil owner yang bertanggung jawab atas akun ini */
    public function ownerAccount(): ?User
    {
        if ($this->isOwner()) return $this;
        // kasir → cari owner melalui outlet yang dipegang
        $outlet = $this->outlet()->with('owner')->first();
        return $outlet?->owner;
    }

    /**
     * Query builder scoped to outlets this user may access:
     *  - admin      → all outlets
     *  - owner      → outlets they own (owner_id = user.id)
     *  - kasir/etc  → only their bound outlet
     */
    public function accessibleOutlets(): Builder
    {
        if ($this->isAdmin()) {
            return Outlet::query();
        }
        if ($this->isOwner()) {
            return Outlet::where('owner_id', $this->id);
        }
        if ($this->outlet_id) {
            return Outlet::where('id', $this->outlet_id);
        }
        return Outlet::whereRaw('0 = 1');
    }

    /**
     * Return the outlet_id that this user is bound to, or null.
     * Kasir with an assigned outlet can only operate on that outlet.
     */
    public function assignedOutletId(): ?int
    {
        return $this->outlet_id ? (int) $this->outlet_id : null;
    }
}
