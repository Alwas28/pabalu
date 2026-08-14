<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_unit_id', 'reason', 'cost', 'started_at', 'finished_at', 'notes'])]
class RentalUnitMaintenance extends Model
{
    protected function casts(): array
    {
        return [
            'started_at'  => 'date',
            'finished_at' => 'date',
        ];
    }

    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class);
    }

    public function isOngoing(): bool
    {
        return $this->finished_at === null;
    }
}
