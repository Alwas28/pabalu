<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['image', 'badge', 'title', 'button_text', 'button_url', 'sort_order', 'is_active'])]
class PromoBanner extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
