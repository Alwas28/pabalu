<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'outlet_id', 'order_id', 'laundry_order_id', 'user_id', 'transaction_number', 'date',
    'subtotal', 'discount_percent', 'discount_amount', 'total',
    'payment_method', 'payment_amount', 'change_amount',
    'reference_number', 'proof_image', 'midtrans_order_id',
    'status', 'notes',
])]
class Transaction extends Model
{
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public static array $paymentLabels = [
        'cash'          => 'Tunai',
        'qris'          => 'QRIS', // legacy, sebelum dipecah jadi QRIS Transfer/QRIS Pay
        'qris_transfer' => 'QRIS Transfer',
        'qris_pay'      => 'QRIS Pay',
        'transfer'      => 'Transfer',
        'card'          => 'Kartu',
    ];

    public function paymentLabel(): string
    {
        return static::$paymentLabels[$this->payment_method] ?? $this->payment_method;
    }

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
        return $this->hasMany(TransactionItem::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_transaction');
    }

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class);
    }
}
