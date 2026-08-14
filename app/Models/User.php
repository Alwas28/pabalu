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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'is_active', 'phone', 'business_name', 'setup_completed_at', 'created_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // Always eager-load role so $user->role is zero extra queries
    protected $with = ['roleRelation'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'is_active'           => 'boolean',
            'setup_completed_at'  => 'datetime',
        ];
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'owner_id');
    }

    public function assignedOutlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_employees');
    }

    public function hasCompletedSetup(): bool
    {
        return $this->role !== 'owner' || !is_null($this->setup_completed_at);
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
