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
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $products = Product::with(['translations', 'media', 'categories'])
            ->orderBy('id')
            ->paginate($request->integer('per_page', 10));

        return new ProductCollection($products);
    }

    public function show(string $id)
    {
        $product = Product::with(['translations', 'media', 'categories'])->find($id);
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
            'type' => ['required', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'required|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'active' => 'required|boolean',
        ]);

        $product = Product::create($validated);
        $product->load(['translations', 'media', 'categories']);

        return response()->json(new ProductResource($product));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['sometimes', Rule::enum(ProductType::class)],
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_limited' => 'sometimes|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if (! empty($validated)) {
            $product->update($validated);
        }

        return response()->json(new ProductResource($product->fresh()->load(['translations', 'media', 'categories'])));
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
}
