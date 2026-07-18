<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryOrder extends Model
{
    protected $fillable = [
        'outlet_id', 'user_id', 'customer_id', 'order_number',
        'customer_name', 'customer_phone', 'weight_kg', 'notes',
        'estimated_done_at', 'status',
        'subtotal', 'discount_amount', 'total',
        'payment_method', 'payment_amount', 'change_amount', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg'        => 'decimal:2',
            'estimated_done_at' => 'datetime',
            'paid_at'          => 'datetime',
        ];
    }

    public static array $statusLabels = [
        'masuk'   => 'Masuk',
        'proses'  => 'Sedang Diproses',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    public static array $statusColors = [
        'masuk'   => 'badge-blue',
        'proses'  => 'badge-amber',
        'selesai' => 'badge-green',
        'diambil' => 'badge-gray',
    ];

    public static array $paymentLabels = [
        'cash'          => 'Tunai',
        'qris_transfer' => 'QRIS Transfer',
        'qris_pay'      => 'QRIS Pay',
        'transfer'      => 'Transfer Bank',
        'card'          => 'Kartu Debit/Kredit',
    ];

    /** Status berikutnya dalam alur kerja. */
    public function nextStatus(): ?string
    {
        return match ($this->status) {
            'masuk'   => 'proses',
            'proses'  => 'selesai',
            default   => null,
        };
    }

    public function nextStatusLabel(): ?string
    {
        $next = $this->nextStatus();
        return $next ? self::$statusLabels[$next] : null;
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaundryOrderItem::class);
    }
}
