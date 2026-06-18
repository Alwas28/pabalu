<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'route_prefix', 'type_code', 'icon', 'description', 'requires_opening_stock', 'track_cogs', 'is_active', 'sort_order'])]
class OutletType extends Model
{
    protected function casts(): array
    {
        return [
            'requires_opening_stock' => 'boolean',
            'track_cogs'             => 'boolean',
            'is_active'              => 'boolean',
        ];
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }
}
