<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'code'                => $this->code,
            'address'             => $this->address,
            'outlet_type'         => $this->outletType?->slug,
            'outlet_type_label'   => $this->outletType?->name,
            'route_prefix'        => $this->rp(),
            'order_mode'          => $this->order_mode,
            'enable_opening_shift'=> (bool) $this->enable_opening_shift,
            'requires_opening_stock' => (bool) ($this->outletType?->requires_opening_stock ?? false),
            'is_active'           => (bool) $this->is_active,
        ];
    }
}
