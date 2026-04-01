<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UnitCollection extends ResourceCollection
{
    public static $wrap = null;

    public $collects = UnitResource::class;

    public function toArray(Request $request): array
    {
        $paginator = $this->resource;

        return [
            'items' => $this->collection->map->toArray($request)->values()->all(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
        ];
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        return [];
    }
}
