<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['outlet_id', 'period_type', 'period_start', 'period_end', 'transaction_total', 'amount', 'note', 'status', 'paid_at', 'created_by'])]
class ProOwnerInvoice extends Model
{
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end'   => 'date',
            'paid_at'      => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Baris pemakaian kode pelunasan yang melunaskan tagihan ini (kalau lunas lewat kode,
    // bukan ditandai admin langsung). Dipakai untuk membedakan pendapatan vs digratiskan.
    public function paymentUsage(): HasOne
    {
        return $this->hasOne(ProInvoicePaymentUsage::class, 'pro_owner_invoice_id');
    }
}
