<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingInvoice extends Model
{
    protected $fillable = [
        'user_id', 'created_by', 'amount', 'description', 'period_label',
        'due_date', 'status', 'payment_ref', 'snap_token',
        'snap_token_expires_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'              => 'date',
            'paid_at'               => 'datetime',
            'snap_token_expires_at' => 'datetime',
            'amount'                => 'decimal:0',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSnapTokenValid(): bool
    {
        return $this->snap_token
            && $this->snap_token_expires_at
            && $this->snap_token_expires_at->isFuture();
    }

    public function isDueSoon(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->diffInDays(now(), false) >= -3;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    public static function activeFor(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('status', 'unpaid')
            ->orderBy('due_date')
            ->first();
    }
}
