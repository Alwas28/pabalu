<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'group_label', 'label', 'type', 'url', 'sort_order', 'is_active'])]
class HomeMenu extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function isMega(): bool
    {
        return $this->type === 'mega';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(HomeMenu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(HomeMenu::class, 'parent_id')->orderBy('sort_order')->orderBy('label');
    }

    /** Sub-menu dikelompokkan per group_label — dipakai untuk render kolom mega menu. */
    public function childrenGrouped()
    {
        return $this->children->groupBy(fn ($child) => $child->group_label ?: 'Lainnya');
    }
}
