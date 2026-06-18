<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['stock_in_id', 'product_id', 'qty', 'notes'])]
class StockInItem extends Model
{
    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
