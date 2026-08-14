<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['outlet_id', 'name', 'phone', 'email', 'address', 'city', 'birth_date', 'gender', 'notes'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function laundryOrders(): HasMany
    {
        return $this->hasMany(LaundryOrder::class);
    }

    public function rentalTransactions(): HasMany
    {
        return $this->hasMany(RentalTransaction::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    /** Sewa hanya boleh dibuat jika outlet punya persyaratan wajib DAN pelanggan sudah "Terverifikasi" untuk semuanya. */
    public function hasVerifiedAllRequiredDocuments(): bool
    {
        $requiredIds = $this->outlet->documentRequirements()->where('status', 'wajib')->pluck('id');

        if ($requiredIds->isEmpty()) {
            return true;
        }

        $verifiedIds = $this->documents()->where('status', 'terverifikasi')->pluck('rental_document_requirement_id');

        return $requiredIds->diff($verifiedIds)->isEmpty();
    }
}
