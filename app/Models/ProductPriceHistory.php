<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'outlet_id', 'changed_by', 'old_price', 'new_price', 'stock_at_change', 'notes'])]
class ProductPriceHistory extends Model
{
    public $timestamps = false;
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'old_price'       => 'integer',
            'new_price'       => 'integer',
            'stock_at_change' => 'integer',
            'created_at'      => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function priceDiff(): int
    {
        return $this->new_price - $this->old_price;
    }

    public function priceDiffPct(): float
    {
        if ($this->old_price === 0) return 0;
        return round(($this->new_price - $this->old_price) / $this->old_price * 100, 1);
    }
}
