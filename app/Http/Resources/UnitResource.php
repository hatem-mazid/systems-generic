<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_group_id' => $this->unit_group_id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'type' => $this->type?->value,
            'color' => $this->color,
            'properties' => $this->properties,
            'active' => $this->active,
            'status' => $this->status,
            'reserved_at' => $this->reserved_at,
            'reserved_by' => $this->reserved_by,
            'current_order_id' => $this->current_order_id,
            'position' => $this->position,
            'price_per_hour' => $this->price_per_hour,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'group' => $this->whenLoaded('group', function () {
                return new UnitGroupResource($this->group);
            }),
            'current_order' => $this->whenLoaded('currentOrder', function () {
                return $this->currentOrder ? [
                    'id' => $this->currentOrder->id,
                    'status' => $this->currentOrder->status,
                    'total' => $this->currentOrder->total,
                    'reserved_at' => $this->currentOrder->reserved_at,
                    'opened_at' => $this->currentOrder->opened_at,
                    'closed_at' => $this->currentOrder->closed_at,
                ] : null;
            }),
        ];
    }
}
