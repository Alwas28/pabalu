<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteItem extends Model
{
    protected $fillable = ['waste_id', 'product_id', 'product_name', 'qty', 'reason', 'notes'];

    public function waste(): BelongsTo
    {
        return $this->belongsTo(Waste::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }
}
