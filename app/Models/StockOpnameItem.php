<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['stock_opname_id', 'product_id', 'system_qty', 'actual_qty', 'notes'])]
class StockOpnameItem extends Model
{
    protected function casts(): array
    {
        return [
            'system_qty' => 'integer',
            'actual_qty' => 'integer',
        ];
    }

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function difference(): int
    {
        return $this->actual_qty - $this->system_qty;
    }
}
