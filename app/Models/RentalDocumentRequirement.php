<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'name', 'status', 'sort_order'])]
class RentalDocumentRequirement extends Model
{
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
