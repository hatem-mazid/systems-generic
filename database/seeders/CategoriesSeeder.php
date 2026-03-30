<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Beverages',
                'description' => 'Drinks and refreshments.',
                'order' => 1,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'key' => 'name', 'value' => 'Beverages'],
                    ['locale' => 'en', 'key' => 'description', 'value' => 'Drinks and refreshments.'],
                    ['locale' => 'ar', 'key' => 'name', 'value' => 'المشروبات'],
                    ['locale' => 'ar', 'key' => 'description', 'value' => 'مشروبات ومنعشات.'],
                ],
            ],
            [
                'name' => 'Main Dishes',
                'description' => 'Hearty meals and signature plates.',
                'order' => 2,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'key' => 'name', 'value' => 'Main Dishes'],
                    ['locale' => 'en', 'key' => 'description', 'value' => 'Hearty meals and signature plates.'],
                    ['locale' => 'ar', 'key' => 'name', 'value' => 'الأطباق الرئيسية'],
                    ['locale' => 'ar', 'key' => 'description', 'value' => 'وجبات رئيسية وأطباق مميزة.'],
                ],
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet treats and bakery items.',
                'order' => 3,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'key' => 'name', 'value' => 'Desserts'],
                    ['locale' => 'en', 'key' => 'description', 'value' => 'Sweet treats and bakery items.'],
                    ['locale' => 'ar', 'key' => 'name', 'value' => 'الحلويات'],
                    ['locale' => 'ar', 'key' => 'description', 'value' => 'حلويات ومخبوزات لذيذة.'],
                ],
            ],
        ];

        foreach ($categories as $payload) {
            $translations = $payload['translations'];
            unset($payload['translations']);

            $category = Category::create($payload);
            $category->translations()->createMany($translations);
        }
    }
}
