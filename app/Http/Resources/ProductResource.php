<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translatedName = $this->t('name', $locale);
        $translatedDescription = $this->t('description', $locale);

        return [
            'id' => $this->id,
            'title' => $translatedName,
            'name' => $translatedName,
            'description' => $translatedDescription,
            'type' => $this->type?->value,
            'price' => $this->price,
            'is_limited' => $this->is_limited,
            'stock_quantity' => $this->stock_quantity,
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
                return $this->media
                    ->sortByDesc(fn ($item) => (bool) $item->getCustomProperty('is_default'))
                    ->values()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'collection_name' => $item->collection_name,
                            'name' => $item->name,
                            'file_name' => $item->file_name,
                            'mime_type' => $item->mime_type,
                            'size' => $item->size,
                            'url' => $item->getUrl(),
                            'is_default' => (bool) $item->getCustomProperty('is_default'),
                        ];
                    })->all();
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    $locale = app()->getLocale();

                    return [
                        'id' => $category->id,
                        'title' => $category->t('name', $locale),
                        'name' => $category->t('name', $locale),
                        'description' => $category->t('description', $locale),
                    ];
                })->values()->all();
            }),
        ];
    }
}
