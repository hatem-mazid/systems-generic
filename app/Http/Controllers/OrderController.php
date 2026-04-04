<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = (int) ($validated['per_page'] ?? $request->integer('per_page', 15));

        $orders = Order::with(['user:id,name', 'unit:id,name', 'items'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return new OrderCollection($orders);
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['user:id,name', 'unit:id,name', 'items']));
    }
}
