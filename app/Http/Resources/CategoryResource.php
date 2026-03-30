<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'order' => $this->order,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'translations' => $this->whenLoaded('translations', function () {
                return $this->translations->map(function ($translation) {
                    return [
                        'id' => $translation->id,
                        'locale' => $translation->locale,
                        'key' => $translation->key,
                        'value' => $translation->value,
                    ];
                })->values()->all();
            }),
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'collection_name' => $item->collection_name,
                        'name' => $item->name,
                        'file_name' => $item->file_name,
                        'mime_type' => $item->mime_type,
                        'size' => $item->size,
                        'url' => $item->getUrl(),
                    ];
                })->values()->all();
            }),
        ];
    }
}
