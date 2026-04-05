<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'status' => 'sometimes|nullable|string|in:open,pending,closed,cancelled',
            'date_from' => 'sometimes|nullable|date',
            'date_to' => [
                'sometimes',
                'nullable',
                'date',
                Rule::when(fn () => $request->filled('date_from'), ['after_or_equal:date_from']),
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? $request->integer('per_page', 15));

        $query = Order::with(['user:id,name', 'unit:id,name', 'items']);

        if (array_key_exists('user_id', $validated) && $validated['user_id'] !== null) {
            $query->where('user_id', $validated['user_id']);
        }

        if (! empty($validated['status'] ?? null)) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['date_from'] ?? null)) {
            $query->where('opened_at', '>=', Carbon::parse($validated['date_from'])->startOfDay());
        }

        if (! empty($validated['date_to'] ?? null)) {
            $query->where('opened_at', '<=', Carbon::parse($validated['date_to'])->endOfDay());
        }

        $orders = $query->orderByDesc('id')->paginate($perPage);

        return new OrderCollection($orders);
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['user:id,name', 'unit:id,name', 'items']));
    }
}
