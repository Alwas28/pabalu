<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaction_id', 'order_id', 'product_id', 'product_name', 'product_price', 'qty', 'subtotal'])]
class TransactionItem extends Model
{
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
