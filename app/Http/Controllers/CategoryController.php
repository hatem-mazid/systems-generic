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
        $categories = Category::with('translations')
            ->orderBy('order')
            ->paginate($request->integer('per_page', 10));

        return new CategoryCollection($categories);
    }

    public function show(string $id)
    {
        $category = Category::with('translations')->find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json(new CategoryResource($category));
    }

    public function store(Request $request)
    {
        $formData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'active' => 'required|boolean',
        ]);

        $category = Category::create($formData)->load('translations');

        return response()->json(new CategoryResource($category));
    }

    public function update(Request $request, string $id)
    {
        $formData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer',
            'active' => 'sometimes|required|boolean',
        ]);

        $category = Category::find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->update($formData);
        $category->load('translations');

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
}
