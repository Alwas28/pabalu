<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pro_invoice_payment_code_id', 'outlet_id', 'pro_owner_invoice_id', 'used_by', 'used_at'])]
class ProInvoicePaymentUsage extends Model
{
    protected function casts(): array
    {
        return ['used_at' => 'datetime'];
    }

    public function code(): BelongsTo
    {
        return $this->belongsTo(ProInvoicePaymentCode::class, 'pro_invoice_payment_code_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ProOwnerInvoice::class, 'pro_owner_invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
