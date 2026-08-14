<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_transaction_id', 'amount', 'method', 'is_fine', 'reference_number', 'photo', 'note', 'paid_at'])]
class RentalPayment extends Model
{
    public const METHODS = [
        'cash'          => 'Tunai',
        'qris_transfer' => 'QRIS Transfer',
        'qris_pay'      => 'QRIS Pay',
        'transfer'      => 'Transfer',
        'card'          => 'Kartu',
        'deposit'       => 'Potongan Deposit',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'is_fine' => 'boolean'];
    }

    public function rentalTransaction(): BelongsTo
    {
        return $this->belongsTo(RentalTransaction::class);
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}