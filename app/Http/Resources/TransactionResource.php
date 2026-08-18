<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'transaction_number'  => $this->transaction_number,
            'date'                => $this->date?->toDateString(),
            'created_at'          => $this->created_at?->toIso8601String(),
            'cashier'             => $this->whenLoaded('user', fn () => $this->user?->name),
            'subtotal'            => (int) $this->subtotal,
            'discount_amount'     => (int) $this->discount_amount,
            'total'               => (int) $this->total,
            'payment_method'      => $this->payment_method,
            'payment_label'       => Transaction::$paymentLabels[$this->payment_method] ?? $this->payment_method,
            'payment_amount'      => (int) $this->payment_amount,
            'change_amount'       => (int) $this->change_amount,
            'reference_number'    => $this->reference_number,
            'proof_image_url'     => $this->proof_image ? Storage::url($this->proof_image) : null,
            'status'              => $this->status,
            'notes'               => $this->notes,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_name'  => $item->product_name,
                'product_price' => (int) $item->product_price,
                'qty'           => (int) $item->qty,
                'subtotal'      => (int) $item->subtotal,
            ])),
        ];
    }
}
