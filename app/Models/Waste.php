<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waste extends Model
{
    protected $fillable = ['outlet_id', 'user_id', 'date', 'notes', 'submitted_at'];

    protected $casts = [
        'date'         => 'date',
        'submitted_at' => 'datetime',
    ];

    public static array $reasons = [
        'sisa'     => 'Sisa / Tidak Terjual',
        'rusak'    => 'Rusak / Cacat',
        'expired'  => 'Kedaluwarsa',
        'lainnya'  => 'Lainnya',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WasteItem::class);
    }
}
