<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Seed products covering all types, stock modes, price edge cases, activity flags,
     * categories, and sample translations (CategoriesSeeder must run first).
     */
    public function run(): void
    {
        $cat = fn (string $name) => Category::where('name', $name)->first()?->id;

        $rows = [
            // --- Physical: limited stock, in stock, active ---
            [
                'name' => 'Espresso',
                'description' => 'Single shot, dark roast.',
                'type' => ProductType::Physical,
                'price' => 3.50 * 1500,
                'is_limited' => true,
                'stock_quantity' => 500,
                'active' => true,
                'categories' => array_filter([$cat('Beverages')]),
                'translations' => [
                    ['locale' => 'en', 'key' => 'name', 'value' => 'Espresso'],
                    ['locale' => 'en', 'key' => 'description', 'value' => 'Single shot, dark roast.'],
                    ['locale' => 'ar', 'key' => 'name', 'value' => 'إسبريسو'],
                    ['locale' => 'ar', 'key' => 'description', 'value' => 'جرعة واحدة، تحميص داكن.'],
                ],
            ],
            [
                'name' => 'Pastry box',
                'description' => 'Assorted pastries (daily selection).',
                'type' => ProductType::Physical,
                'price' => 12.00 * 1500,
                'is_limited' => true,
                'stock_quantity' => 40,
                'active' => true,
                'categories' => array_filter([$cat('Desserts')]),
            ],
            [
                'name' => 'Chef burger',
                'description' => 'Signature beef burger with fries.',
                'type' => ProductType::Physical,
                'price' => 18.50 * 1500,
                'is_limited' => true,
                'stock_quantity' => 25,
                'active' => true,
                'categories' => array_filter([$cat('Main Dishes')]),
            ],

            // --- Physical: unlimited inventory (no stock tracking) ---
            [
                'name' => 'House water',
                'description' => 'Complimentary still or sparkling.',
                'type' => ProductType::Physical,
                'price' => 0.00,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
                'categories' => array_filter([$cat('Beverages')]),
            ],

            // --- Physical: limited, zero stock (out of stock), still listed ---
            [
                'name' => 'Seasonal tart',
                'description' => 'Rotates weekly; restock pending.',
                'type' => ProductType::Physical,
                'price' => 8.00 * 1500,
                'is_limited' => true,
                'stock_quantity' => 0,
                'active' => true,
                'categories' => array_filter([$cat('Desserts')]),
            ],

            // --- Physical: nullable description ---
            [
                'name' => 'Mystery snack',
                'description' => null,
                'type' => ProductType::Physical,
                'price' => 4.00 * 1500,
                'is_limited' => true,
                'stock_quantity' => 100,
                'active' => true,
                'categories' => array_filter([$cat('Desserts'), $cat('Main Dishes')]),
            ],

            // --- Physical: nullable price (e.g. quote at counter) ---
            [
                'name' => 'Market fish',
                'description' => 'Daily catch; price varies.',
                'type' => ProductType::Physical,
                'price' => null,
                'is_limited' => true,
                'stock_quantity' => 8,
                'active' => true,
                'categories' => array_filter([$cat('Main Dishes')]),
            ],

            // --- Physical: inactive / archived catalog row ---
            [
                'name' => 'Archived item',
                'description' => 'Inactive catalog entry for testing.',
                'type' => ProductType::Physical,
                'price' => 9.99 * 1500,
                'is_limited' => true,
                'stock_quantity' => 0,
                'active' => false,
            ],

            // --- Service (fixed price): standard ---
            [
                'name' => 'Room setup fee',
                'description' => 'One-time setup for private events.',
                'type' => ProductType::ServiceFixed,
                'price' => 75.00 * 1500,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
            ],

            // --- Service (fixed): zero price ---
            [
                'name' => 'Complimentary coat check',
                'description' => 'Fixed service with no charge.',
                'type' => ProductType::ServiceFixed,
                'price' => 0.00 * 1500,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
            ],

            // --- Service (timer): hourly-style rate ---
            [
                'name' => 'Private chef',
                'description' => 'Billed per hour; minimum 2 hours.',
                'type' => ProductType::ServiceTimer,
                'price' => 45.00 * 1500,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
            ],

            // --- Service (timer): price to be set / quote ---
            [
                'name' => 'AV technician',
                'description' => 'On-site support; rate confirmed before start.',
                'type' => ProductType::ServiceTimer,
                'price' => null,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
            ],
        ];

        foreach ($rows as $row) {
            $translations = $row['translations'] ?? [];
            $categories = array_values(array_filter($row['categories'] ?? []));
            unset($row['translations'], $row['categories']);

            $product = Product::create($row);

            if ($categories !== []) {
                $product->categories()->attach($categories);
            }

            if ($translations !== []) {
                $product->translations()->createMany($translations);
            }
        }
    }
}
