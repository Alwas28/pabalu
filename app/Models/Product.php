<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'outlet_id', 'category_id', 'name', 'sku', 'unit', 'description', 'image',
    'price', 'cost_price', 'stock', 'min_stock', 'sort_order', 'is_active',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'price'      => 'integer',
            'cost_price' => 'integer',
            'stock'      => 'integer',
            'min_stock'  => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->orderByDesc('created_at');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
