<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'opened_at'     => $this->opened_at?->toIso8601String(),
            'closed_at'     => $this->closed_at?->toIso8601String(),
            'opening_cash'  => (int) $this->opening_cash,
            'closing_cash'  => $this->closing_cash !== null ? (int) $this->closing_cash : null,
            'expected_cash' => $this->expected_cash !== null ? (int) $this->expected_cash : null,
            'notes'         => $this->notes,
            'is_active'     => is_null($this->closed_at),
        ];
    }
}
