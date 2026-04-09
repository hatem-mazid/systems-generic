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
            'reserved_at' => $this->reserved_at,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'unit_name' => $this->whenLoaded('unit', fn () => $this->unit?->name),
            'items' => $this->whenLoaded('items', function () {
                $locale = app()->getLocale();

                return $this->items->map(function ($item) use ($locale) {
                    $product = $item->relationLoaded('product') ? $item->product : null;
                    $translatedName = $item->name;
                    if ($product && $product->relationLoaded('translations')) {
                        $translatedName = $product->t('name', $locale) ?: $item->name;
                    } elseif ($item->type === 'unitFees') {
                        $meta = is_array($item->meta) ? $item->meta : [];
                        $unitName = $meta['unit_name'] ?? null;

                        if (! $unitName && is_string($item->name)) {
                            if (preg_match('/^Unit fee \((.*)\)$/', $item->name, $matches) === 1) {
                                $unitName = $matches[1];
                            }
                        }

                        $translatedName = $unitName
                            ? __('messages.unit_fee_with_name', ['name' => $unitName])
                            : __('messages.unit_fee');
                    }

                    $imageUrl = null;
                    if ($product && $product->relationLoaded('media')) {
                        $m = $product->media
                            ->sortByDesc(fn ($x) => (bool) $x->getCustomProperty('is_default'))
                            ->first();
                        if ($m) {
                            $imageUrl = $m->getUrl();
                        }
                    }

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $translatedName,
                        'notes' => $item->notes,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                        'type' => $item->type,
                        'meta' => $item->meta,
                        'image' => $imageUrl,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                })->values()->all();
            }),
        ];
    }
}
