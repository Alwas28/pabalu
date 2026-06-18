<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description', 'is_system'])]
class Role extends Model
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function getPermissionSlugsAttribute(): array
    {
        return $this->permissions->pluck('slug')->toArray();
    }
}
