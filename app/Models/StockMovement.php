<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'outlet_id',
        'product_id',
        'type',
        'qty',
        'tanggal',
        'keterangan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'qty'     => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung stok terkini sebuah produk di outlet tertentu pada tanggal tertentu.
     */
    public static function currentStock(int $outletId, int $productId, string $tanggal): int
    {
        $opening = self::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->where('type', 'opening')
            ->where('tanggal', $tanggal)
            ->sum('qty');

        $in = self::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->where('type', 'in')
            ->where('tanggal', $tanggal)
            ->sum('qty');

        $waste = self::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->where('type', 'waste')
            ->where('tanggal', $tanggal)
            ->sum('qty');

        $sold = TransactionItem::whereHas('transaction', fn($q) =>
                $q->where('outlet_id', $outletId)
                  ->where('tanggal', $tanggal)
                  ->where('status', 'paid')
            )
            ->where('product_id', $productId)
            ->sum('qty');

        return max(0, $opening + $in - $waste - $sold);
    }
}
