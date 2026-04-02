<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Espresso',
                'description' => 'Single shot, dark roast.',
                'type' => ProductType::Physical,
                'price' => 3.50,
                'is_limited' => true,
                'stock_quantity' => 500,
                'active' => true,
            ],
            [
                'name' => 'Pastry box',
                'description' => 'Assorted pastries (daily selection).',
                'type' => ProductType::Physical,
                'price' => 12.00,
                'is_limited' => true,
                'stock_quantity' => 40,
                'active' => true,
            ],
            [
                'name' => 'Room setup fee',
                'description' => 'One-time setup for private events.',
                'type' => ProductType::ServiceFixed,
                'price' => 75.00,
                'is_limited' => false,
                'stock_quantity' => null,
                'active' => true,
            ],
            [
                'name' => 'Archived item',
                'description' => 'Inactive catalog entry for testing.',
                'type' => ProductType::Physical,
                'price' => 9.99,
                'is_limited' => true,
                'stock_quantity' => 0,
                'active' => false,
            ],
        ];

        foreach ($products as $row) {
            Product::create($row);
        }
    }
}
