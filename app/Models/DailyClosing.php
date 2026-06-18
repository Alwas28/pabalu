<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    protected $fillable = [
        'outlet_id', 'user_id', 'date',
        'total_transactions', 'total_revenue', 'total_discount',
        'total_expense', 'cash_in', 'qris_in', 'transfer_in', 'card_in',
        'notes', 'stocks_snapshot', 'closed_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'closed_at' => 'datetime',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
