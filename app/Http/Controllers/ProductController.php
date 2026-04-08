<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'type' => ['sometimes', 'nullable', Rule::enum(ProductType::class)],
            'active' => 'sometimes|boolean',
            'search' => 'sometimes|nullable|string|max:255',
        ]);

        $query = Product::with(['translations', 'media', 'categories.translations']);

        if (! empty($validated['category_id'] ?? null)) {
            $categoryId = $validated['category_id'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if (! empty($validated['type'] ?? null)) {
            $query->where('type', $validated['type']);
        }

        if (array_key_exists('active', $validated)) {
            $query->where('active', $validated['active']);
        }

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $perPage = (int) ($validated['per_page'] ?? $request->integer('per_page', 12));

        $products = $query->orderBy('id')->paginate($perPage);

        return new ProductCollection($products);
    }

    public function show(string $id)
    {
        $product = Product::with(['translations', 'media', 'categories.translations'])->find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(new ProductResource($product));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'type' => ['required', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'required|boolean',
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:0',
                Rule::requiredIf(fn () => $request->boolean('is_limited')),
            ],
            'active' => 'required|boolean',
            'translations' => 'nullable|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'] ?? null;
        $categoryId = $validated['category_id'] ?? null;
        unset($validated['translations']);
        unset($validated['category_id']);

        $product = Product::create($validated);
        $this->syncProductCategory($product, $categoryId);
        $this->syncProductTranslations($product, $translations);
        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'type' => ['sometimes', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'sometimes|boolean',
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:0',
                Rule::requiredIf(function () use ($request, $product) {
                    $limited = $request->has('is_limited')
                        ? $request->boolean('is_limited')
                        : $product->is_limited;

                    return (bool) $limited;
                }),
            ],
            'active' => 'sometimes|boolean',
            'translations' => 'sometimes|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'] ?? null;
        $hasCategory = array_key_exists('category_id', $validated);
        $categoryId = $validated['category_id'] ?? null;
        unset($validated['translations']);
        unset($validated['category_id']);

        if (! empty($validated)) {
            $product->update($validated);
        }
        if ($hasCategory) {
            $this->syncProductCategory($product, $categoryId);
        }
        $this->syncProductTranslations($product, $translations);
        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->noContent();
    }

    /**
     * Append an image to the product media library (default collection).
     */
    public function storeMedia(Request $request, Product $product)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'collection' => ['sometimes', 'string', 'max:255', 'in:default'],
        ]);

        $media = $product
            ->addMediaFromRequest('file')
            ->toMediaCollection('default');

        $hasDefault = $product->media()->get()->contains(
            fn ($m) => (bool) $m->getCustomProperty('is_default')
        );

        if (! $hasDefault) {
            $media->setCustomProperty('is_default', true);
            $media->save();
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * Remove a single media file from the product.
     */
    public function destroyMedia(Request $request, Product $product)
    {
        $validated = $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ]);

        $media = $product->media()->where('id', $validated['media_id'])->firstOrFail();
        $wasDefault = (bool) $media->getCustomProperty('is_default');
        $media->delete();

        if ($wasDefault) {
            $next = $product->media()->orderBy('id')->first();
            if ($next) {
                $next->setCustomProperty('is_default', true);
                $next->save();
            }
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * Mark one media item as the default (preview) image for listings and cards.
     */
    public function setDefaultMedia(Request $request, Product $product)
    {
        $validated = $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        $target = $product->media()->where('id', $validated['media_id'])->firstOrFail();

        foreach ($product->media as $m) {
            $m->setCustomProperty('is_default', $m->id === $target->id);
            $m->save();
        }

        $product->load(['translations', 'media', 'categories.translations']);

        return response()->json(new ProductResource($product));
    }

    /**
     * @param  array<string, array<string, string|null>>|null  $translations
     */
    private function syncProductTranslations(Product $product, ?array $translations): void
    {
        if ($translations === null) {
            return;
        }

        foreach ($translations as $locale => $fields) {
            if (! is_array($fields) || $locale === 'en') {
                continue;
            }

            foreach (['name', 'description'] as $key) {
                if (! array_key_exists($key, $fields)) {
                    continue;
                }

                $value = $fields[$key];

                if ($value === null || $value === '') {
                    $product->translations()
                        ->where('locale', $locale)
                        ->where('key', $key)
                        ->delete();
                } else {
                    $product->translations()->updateOrCreate(
                        ['locale' => $locale, 'key' => $key],
                        ['value' => $value]
                    );
                }
            }
        }
    }

    private function syncProductCategory(Product $product, ?int $categoryId): void
    {
        if ($categoryId === null) {
            $product->categories()->sync([]);

            return;
        }

        $product->categories()->sync([$categoryId]);
    }
}
