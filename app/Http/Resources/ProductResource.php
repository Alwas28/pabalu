<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'category_id' => $this->category_id,
            'name'        => $this->name,
            'sku'         => $this->sku,
            'unit'        => $this->unit,
            'price'       => (int) $this->price,
            'stock'       => $this->stock !== null ? (int) $this->stock : null,
            'image_url'   => $this->image ? Storage::url($this->image) : null,
        ];
    }
}
