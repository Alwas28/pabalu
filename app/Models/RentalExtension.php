<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_transaction_id', 'previous_end_at', 'new_end_at', 'additional_amount', 'notes'])]
class RentalExtension extends Model
{
    protected function casts(): array
    {
        return [
            'previous_end_at' => 'datetime',
            'new_end_at'      => 'datetime',
        ];
    }

    public function rentalTransaction(): BelongsTo
    {
        return $this->belongsTo(RentalTransaction::class);
    }
}
