<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'outlet_id', 'user_id', 'order_number', 'table_number', 'customer_name',
        'status', 'subtotal', 'discount_percent', 'discount_amount',
        'total', 'notes', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public static array $statuses = [
        'pending'   => ['label' => 'Menunggu Review', 'color' => '#a78bfa', 'bg' => 'rgba(167,139,250,.15)', 'icon' => 'fa-hourglass-half'],
        'open'      => ['label' => 'Menunggu',        'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,.15)',  'icon' => 'fa-clock'],
        'preparing' => ['label' => 'Diproses',        'color' => '#60a5fa', 'bg' => 'rgba(96,165,250,.15)',  'icon' => 'fa-fire-burner'],
        'served'    => ['label' => 'Diantarkan',      'color' => '#34d399', 'bg' => 'rgba(52,211,153,.15)',  'icon' => 'fa-bell-concierge'],
        'paid'      => ['label' => 'Lunas',           'color' => '#94a3b8', 'bg' => 'rgba(148,163,184,.12)', 'icon' => 'fa-circle-check'],
        'cancelled' => ['label' => 'Dibatalkan',      'color' => '#f87171', 'bg' => 'rgba(248,113,113,.12)', 'icon' => 'fa-ban'],
    ];

    public function statusMeta(): array
    {
        return static::$statuses[$this->status] ?? static::$statuses['open'];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'order_transaction');
    }

    public function getTransactionAttribute(): ?Transaction
    {
        return $this->transactions->first();
    }

    public function allServed(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(fn($i) => $i->is_served);
    }
}
