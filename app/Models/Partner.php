<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'logo', 'website_url', 'is_active', 'sort_order'])]
class Partner extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
