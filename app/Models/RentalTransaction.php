<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'outlet_id', 'customer_id', 'rental_unit_id', 'rental_type', 'order_number',
    'start_at', 'end_at', 'total_amount', 'deposit_amount', 'fine_amount',
    'condition_note', 'status', 'notes', 'returned_at', 'refunded_at', 'refund_amount',
])]
class RentalTransaction extends Model
{
    public const TYPES = [
        'per_jam'   => 'Per Jam',
        'per_hari'  => 'Per Hari',
        'per_bulan' => 'Per Bulan',
    ];

    protected function casts(): array
    {
        return [
            'start_at'    => 'datetime',
            'end_at'      => 'datetime',
            'returned_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function rentalTypeLabel(): string
    {
        return self::TYPES[$this->rental_type] ?? $this->rental_type;
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class)->orderByDesc('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentalPayment::class)->orderBy('paid_at');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'aktif' && $this->end_at->isPast();
    }

    /** Bagian deposit yang sudah dipakai untuk melunasi biaya sewa (bukan untuk denda). */
    public function depositApplied(): int
    {
        return $this->relationLoaded('payments')
            ? $this->payments->where('method', 'deposit')->sum('amount')
            : $this->payments()->where('method', 'deposit')->sum('amount');
    }

    /** Sisa deposit yang belum dipakai untuk membayar biaya sewa (belum dipotong denda). */
    public function depositAvailable(): int
    {
        return max(0, $this->deposit_amount - $this->depositApplied());
    }

    /** Total denda yang sudah dibayar (baik lewat metode pembayaran maupun deposit). */
    public function finePaid(): int
    {
        return $this->relationLoaded('payments')
            ? $this->payments->where('is_fine', true)->sum('amount')
            : $this->payments()->where('is_fine', true)->sum('amount');
    }

    /** Sisa denda yang belum dibayar. */
    public function fineRemaining(): int
    {
        return max(0, $this->fine_amount - $this->finePaid());
    }

    /** Sisa deposit yang akan dikembalikan ke pelanggan (setelah dipakai untuk biaya sewa & dipotong denda yang belum dibayar). */
    public function depositBalance(): int
    {
        return $this->depositAvailable() - $this->fineRemaining();
    }

    public function paidAmount(): int
    {
        return $this->relationLoaded('payments')
            ? $this->payments->sum('amount')
            : $this->payments()->sum('amount');
    }

    public function remainingAmount(): int
    {
        return max(0, $this->total_amount - $this->paidAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }

    public function paymentStatusLabel(): string
    {
        if ($this->paidAmount() <= 0) return 'Belum Bayar';
        return $this->isFullyPaid() ? 'Lunas' : 'DP';
    }

    /** Sewa ini sudah selesai, punya sisa deposit yang harus dikembalikan, dan belum ditandai di-refund. */
    public function needsRefund(): bool
    {
        return $this->status === 'selesai' && $this->refunded_at === null && $this->depositBalance() > 0;
    }
}
