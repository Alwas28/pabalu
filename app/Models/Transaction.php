<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'outlet_id',
        'kasir_id',
        'nomor_transaksi',
        'tanggal',
        'total',
        'bayar',
        'kembalian',
        'keterangan',
        'status',
        'metode_bayar',
        'bukti_bayar',
        'payment_ref',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'   => 'date',
            'total'     => 'decimal:2',
            'bayar'     => 'decimal:2',
            'kembalian' => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Generate nomor transaksi: TRX-YYYYMMDD-XXXX
     */
    public static function generateNomor(int $outletId, string $tanggal): string
    {
        $prefix = 'TRX-' . str_replace('-', '', $tanggal) . '-';

        // Sequence harus global (bukan per-outlet) karena nomor_transaksi unique secara global
        $last = self::where('nomor_transaksi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $seq = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
