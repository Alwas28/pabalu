<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const KATEGORI = [
        'operasional'  => 'Operasional',
        'bahan_baku'   => 'Bahan Baku',
        'gaji'         => 'Gaji / Honor',
        'utilitas'     => 'Listrik / Air / Gas',
        'promosi'      => 'Promosi / Iklan',
        'peralatan'    => 'Peralatan',
        'lainnya'      => 'Lainnya',
    ];

    protected $fillable = [
        'outlet_id',
        'user_id',
        'tanggal',
        'kategori',
        'keterangan',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah'  => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
