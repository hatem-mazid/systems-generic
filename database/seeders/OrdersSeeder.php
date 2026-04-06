<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds. Expects UnitsSeeder, ProductsSeeder, and a waiter user (e.g. DatabaseSeeder).
     */
    public function run(): void
    {
        $waiter = User::where('email', 'waiter@spark.com')->first();

        // Seed only orders already attached to units (via units.current_order_id).
        $definitions = [
            [
                'unit_name' => 'T3',
                'items' => [
                    ['product_name' => 'Espresso', 'quantity' => 2],
                    ['product_name' => 'Chef burger', 'quantity' => 1],
                ],
            ],
            [
                'unit_name' => 'T2',
                'items' => [
                    ['product_name' => 'Espresso', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($definitions as $def) {
            $unitName = $def['unit_name'];
            $items = $def['items'];

            $unit = Unit::with('currentOrder')
                ->where('name', $unitName)
                ->first();

            if (! $unit || ! $unit->currentOrder) {
                continue;
            }

            $order = $unit->currentOrder;
            $order->user_id = $waiter?->id;
            $order->save();
            $order->items()->delete();

            foreach ($items as $line) {
                $product = Product::where('name', $line['product_name'])->first();
                $quantity = (int) $line['quantity'];
                $price = (float) ($product?->price ?? 0);
                $lineTotal = round($price * $quantity, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product?->id,
                    'name' => $product?->name ?? $line['product_name'],
                    'notes' => null,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                    'type' => 'product',
                    'meta' => null,
                ]);
            }

            $order->recalculateTotal();
        }
    }
}
