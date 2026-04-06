<?php

namespace Database\Seeders;

use App\Models\Order;
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

        $unitId = fn (string $name) => Unit::where('name', $name)->value('id');

        $definitions = [
            [
                'unit_id' => $unitId('T3'),
                'user_id' => $waiter?->id,
                'status' => 'active',
                'opened_at' => now()->subHour(),
                'closed_at' => null,
                'items' => [
                    ['product_name' => 'Espresso', 'quantity' => 2],
                    ['product_name' => 'Chef burger', 'quantity' => 1],
                ],
            ],
            [
                'unit_id' => $unitId('T1'),
                'user_id' => $waiter?->id,
                'status' => 'reserved',
                'opened_at' => now()->subMinutes(20),
                'closed_at' => null,
                'items' => [
                    ['product_name' => 'Espresso', 'quantity' => 1],
                ],
            ],
            [
                'unit_id' => $unitId('T2'),
                'user_id' => $waiter?->id,
                'status' => 'closed',
                'opened_at' => now()->subDay()->setTime(18, 30),
                'closed_at' => now()->subDay()->setTime(21, 15),
                'items' => [
                    ['product_name' => 'Pastry box', 'quantity' => 1],
                    ['product_name' => 'Chef burger', 'quantity' => 2],
                    ['product_name' => 'House water', 'quantity' => 3],
                ],
            ],
            [
                'unit_id' => $unitId('G2'),
                'user_id' => $waiter?->id,
                'status' => 'cancelled',
                'opened_at' => now()->subHours(3),
                'closed_at' => now()->subHours(2),
                'items' => [],
            ],
        ];

        foreach ($definitions as $def) {
            if ($def['unit_id'] === null) {
                continue;
            }

            $items = $def['items'];
            unset($def['items']);

            $def['total'] = 0;

            $order = Order::create($def);

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
