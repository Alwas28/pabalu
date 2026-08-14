<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'rental_document_requirement_id', 'photo', 'status', 'notes', 'verified_at'])]
class CustomerDocument extends Model
{
    public const STATUSES = [
        'menunggu'      => 'Menunggu',
        'terverifikasi' => 'Terverifikasi',
        'ditolak'       => 'Ditolak',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(RentalDocumentRequirement::class, 'rental_document_requirement_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
