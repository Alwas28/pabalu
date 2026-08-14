<?php

namespace App\Http\Resources;

use App\Models\LaundryOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaundryOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'customer_name'     => $this->customer_name,
            'customer_phone'    => $this->customer_phone,
            'weight_kg'         => $this->weight_kg !== null ? (float) $this->weight_kg : null,
            'notes'             => $this->notes,
            'estimated_done_at' => $this->estimated_done_at?->toIso8601String(),
            'status'            => $this->status,
            'status_label'      => LaundryOrder::$statusLabels[$this->status] ?? $this->status,
            'next_status'       => $this->nextStatus(),
            'next_status_label' => $this->nextStatusLabel(),
            'subtotal'          => (int) $this->subtotal,
            'discount_amount'   => (int) $this->discount_amount,
            'total'             => (int) $this->total,
            'payment_method'    => $this->payment_method,
            'payment_amount'    => $this->payment_amount !== null ? (int) $this->payment_amount : null,
            'change_amount'     => $this->change_amount !== null ? (int) $this->change_amount : null,
            'paid_at'           => $this->paid_at?->toIso8601String(),
            'created_at'        => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_name'  => $item->product_name,
                'product_price' => (int) $item->product_price,
                'qty'           => (float) $item->qty,
                'unit'          => $item->unit,
                'subtotal'      => (int) $item->subtotal,
                'item_notes'    => $item->item_notes,
            ])),
        ];
    }
}
