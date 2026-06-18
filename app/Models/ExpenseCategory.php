<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseCategory extends Model
{
    protected $fillable = ['outlet_id', 'slug', 'label'];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
