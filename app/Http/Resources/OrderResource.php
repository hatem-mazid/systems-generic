<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'total' => $this->total,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'unit_name' => $this->whenLoaded('unit', fn () => $this->unit?->name),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $product = $item->relationLoaded('product') ? $item->product : null;
                    $primary = null;
                    if ($product && $product->relationLoaded('media')) {
                        $m = $product->media
                            ->sortByDesc(fn ($x) => (bool) $x->getCustomProperty('is_default'))
                            ->first();
                        if ($m) {
                            $primary = [
                                'id' => $m->id,
                                'collection_name' => $m->collection_name,
                                'name' => $m->name,
                                'file_name' => $m->file_name,
                                'mime_type' => $m->mime_type,
                                'size' => $m->size,
                                'url' => $m->getUrl(),
                            ];
                        }
                    }

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'notes' => $item->notes,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                        'type' => $item->type,
                        'meta' => $item->meta,
                        'image' => $primary,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                })->values()->all();
            }),
        ];
    }
}
