<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'outlet_id', 'user_id', 'opened_by', 'closed_by',
    'opened_at', 'closed_at',
    'opening_cash', 'closing_cash', 'expected_cash',
    'cash_in', 'total_transactions', 'total_revenue', 'total_expense',
    'notes',
])]
class Shift extends Model
{
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isActive(): bool
    {
        return $this->closed_at === null;
    }

    public function selisih(): ?int
    {
        if ($this->closing_cash === null || $this->expected_cash === null) {
            return null;
        }
        return (int) $this->closing_cash - (int) $this->expected_cash;
    }

    public function duration(): ?string
    {
        $end = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($end);
        if ($diff->h > 0) {
            return $diff->h . ' jam ' . $diff->i . ' mnt';
        }
        return $diff->i . ' mnt';
    }
}
