<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['outlet_type_id', 'name', 'icon', 'description', 'sort_order', 'is_active', 'is_featured'])]
class Category extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function outletType(): BelongsTo
    {
        return $this->belongsTo(OutletType::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function homeCategories(): BelongsToMany
    {
        return $this->belongsToMany(HomeCategory::class, 'home_category_category');
    }
}
