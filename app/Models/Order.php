<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'outlet_id', 'order_number', 'customer_name', 'customer_phone',
        'catatan', 'subtotal', 'order_status', 'payment_token',
        'processed_at', 'ready_at', 'completed_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'     => 'integer',
            'processed_at' => 'datetime',
            'ready_at'     => 'datetime',
            'completed_at' => 'datetime',
            'paid_at'      => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Generate nomor order (unik per outlet per hari) ──
    public static function generateNumber(int $outletId): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-' . $outletId;

        $last = self::where('outlet_id', $outletId)
            ->where('order_number', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('order_number');

        $seq = $last ? (int) substr(strrchr($last, '-'), 1) : 0;

        return $prefix . '-' . str_pad($seq + 1, 3, '0', STR_PAD_LEFT);
    }

    // ── Label & warna status ──────────────────────────────
    public static function statusLabel(string $status): string
    {
        return [
            'pending_payment' => 'Menunggu Pembayaran',
            'pending'         => 'Menunggu',
            'processing'      => 'Diproses',
            'ready'           => 'Siap Diambil',
            'completed'       => 'Selesai',
            'cancelled'       => 'Dibatalkan',
        ][$status] ?? $status;
    }

    public static function statusColor(string $status): string
    {
        return [
            'pending_payment' => 'badge-blue',
            'pending'         => 'badge-amber',
            'processing'      => 'badge-blue',
            'ready'           => 'badge-green',
            'completed'       => 'badge-gray',
            'cancelled'       => 'badge-red',
        ][$status] ?? 'badge-gray';
    }

    // ── Transisi status ───────────────────────────────────
    public function nextStatus(): ?string
    {
        return [
            'pending'    => 'processing',
            'processing' => 'ready',
            'ready'      => 'completed',
        ][$this->order_status] ?? null;
    }

    public function nextLabel(): ?string
    {
        return [
            'pending'    => 'Proses',
            'processing' => 'Siap Diambil',
            'ready'      => 'Selesai',
        ][$this->order_status] ?? null;
    }
}
