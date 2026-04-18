<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'outlet_id',
        'category_id',
        'kode',
        'nama',
        'deskripsi',
        'gambar',
        'harga_jual',
        'satuan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'decimal:2',
            'is_active'  => 'boolean',
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
}
