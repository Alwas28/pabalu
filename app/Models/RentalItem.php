<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['outlet_id', 'category_id', 'name', 'description', 'is_active', 'sort_order'])]
class RentalItem extends Model
{
    public const MAX_IMAGES = 4;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RentalItemImage::class)->orderBy('sort_order');
    }

    public function units(): HasMany
    {
        return $this->hasMany(RentalUnit::class);
    }
}
