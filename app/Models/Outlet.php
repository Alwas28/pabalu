<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Outlet extends Model
{
    protected $fillable = [
        'owner_id', 'nama', 'slug',
        'alamat', 'telepon', 'email', 'keterangan', 'is_active',
        'payment_gateway_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active'                => 'boolean',
            'payment_gateway_enabled'  => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
