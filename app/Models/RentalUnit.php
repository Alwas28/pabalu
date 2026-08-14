<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['rental_item_id', 'code', 'condition', 'status', 'notes'])]
class RentalUnit extends Model
{
    public const STATUSES = [
        'tersedia'    => 'Tersedia',
        'disewa'      => 'Disewa',
        'maintenance' => 'Maintenance',
        'rusak'       => 'Rusak',
    ];

    public function rentalItem(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(RentalUnitMaintenance::class)->orderByDesc('started_at');
    }

    public function rentalTransactions(): HasMany
    {
        return $this->hasMany(RentalTransaction::class);
    }
}
