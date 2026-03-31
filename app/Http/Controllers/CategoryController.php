<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with(['translations', 'media'])
            ->orderBy('order')
            ->paginate($request->integer('per_page', 10));

        return new CategoryCollection($categories);
    }

    public function show(string $id)
    {
        $category = Category::with(['translations', 'media'])->find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json(new CategoryResource($category));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'active' => 'required|boolean',
            'translations' => 'nullable|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'] ?? null;
        unset($validated['translations']);

        $category = Category::create($validated);
        $this->syncCategoryTranslations($category, $translations);
        $category->load(['translations', 'media']);

        return response()->json(new CategoryResource($category));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer',
            'active' => 'sometimes|required|boolean',
            'translations' => 'sometimes|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.name' => 'nullable|string|max:255',
            'translations.ar.description' => 'nullable|string',
        ]);

        $category = Category::find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $translations = $validated['translations'] ?? null;
        unset($validated['translations']);

        if (! empty($validated)) {
            $category->update($validated);
        }
        $this->syncCategoryTranslations($category, $translations);
        $category->load(['translations', 'media']);

        return response()->json(new CategoryResource($category));
    }

    public function destroy(string $id)
    {
        $category = Category::with('translations')->find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(new CategoryResource($category));
    }

    /**
     * Add a file to a Spatie media collection, or replace the collection when replace=true.
     */
    public function storeMedia(Request $request, Category $category)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'collection' => ['sometimes', 'string', 'max:255', 'in:image'],
            'replace' => ['sometimes', 'boolean'],
        ]);

        $collectionName = $validated['collection'] ?? 'image';

        if ($request->boolean('replace')) {
            $category->clearMediaCollection($collectionName);
        }

        $category
            ->addMediaFromRequest('file')
            ->toMediaCollection($collectionName);

        $category->load(['translations', 'media']);

        return response()->json(new CategoryResource($category));
    }

    /**
     * Remove all media from a Spatie collection (default: image).
     */
    public function destroyMedia(Request $request, Category $category)
    {
        $validated = $request->validate([
            'collection' => ['sometimes', 'string', 'max:255', 'in:image'],
        ]);

        $collectionName = $validated['collection'] ?? 'image';

        $category->clearMediaCollection($collectionName);
        $category->load(['translations', 'media']);

        return response()->json(new CategoryResource($category));
    }

    /**
     * Persist locale-specific strings (e.g. Arabic) on the translations table.
     * English uses the main `categories.name` / `categories.description` columns.
     *
     * @param  array<string, array<string, string|null>>|null  $translations
     */
    private function syncCategoryTranslations(Category $category, ?array $translations): void
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
                    $category->translations()
                        ->where('locale', $locale)
                        ->where('key', $key)
                        ->delete();
                } else {
                    $category->translations()->updateOrCreate(
                        ['locale' => $locale, 'key' => $key],
                        ['value' => $value]
                    );
                }
            }
        }
    }
}
