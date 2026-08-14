<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'route_prefix', 'type_code', 'icon', 'description',
    'requires_opening_stock', 'track_cogs', 'is_active', 'sort_order',
    'default_order_mode', 'default_enable_opening_shift', 'default_enable_barcode_scanner',
])]
class OutletType extends Model
{
    protected function casts(): array
    {
        return [
            'requires_opening_stock'          => 'boolean',
            'track_cogs'                      => 'boolean',
            'is_active'                       => 'boolean',
            'default_enable_opening_shift'    => 'boolean',
            'default_enable_barcode_scanner'  => 'boolean',
        ];
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
