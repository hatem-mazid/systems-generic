<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCan('order index');

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'status' => 'sometimes|nullable|string|in:active,reserved,open,pending,closed,cancelled',
            'date_from' => 'sometimes|nullable|date',
            'date_to' => [
                'sometimes',
                'nullable',
                'date',
                Rule::when(fn () => $request->filled('date_from'), ['after_or_equal:date_from']),
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? $request->integer('per_page', 15));

        $query = Order::with([
            'user:id,name',
            'unit:id,name',
            'items.product.translations',
            'items.product.media',
        ]);

        if (array_key_exists('user_id', $validated) && $validated['user_id'] !== null) {
            $query->where('user_id', $validated['user_id']);
        }

        if (! empty($validated['status'] ?? null)) {
            $status = $validated['status'];
            if ($status === 'active') {
                $query->whereIn('status', ['active', 'open']);
            } elseif ($status === 'reserved') {
                $query->whereIn('status', ['reserved', 'pending']);
            } else {
                $query->where('status', $status);
            }
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
        $this->ensureCan('order index');

        return new OrderResource($order->load([
            'user:id,name',
            'unit:id,name',
            'items.product.translations',
            'items.product.media',
        ]));
    }

    public function storeItem(Request $request, Order $order)
    {
        $this->ensureCan('order edit');

        if (! $order->isOpen()) {
            return response()->json(['message' => 'Order is not active.'], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'sometimes|integer|min:1|max:9999',
            'notes' => 'nullable|string|max:2000',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $product = Product::find($validated['product_id']);
        if (! $product || ! $product->active) {
            return response()->json(['message' => 'Product not available.'], 422);
        }

        return DB::transaction(function () use ($order, $validated, $quantity, $product) {
            $product = Product::query()->lockForUpdate()->find($product->id);
            if (! $product || ! $product->active) {
                return response()->json(['message' => 'Product not available.'], 422);
            }

            if ($product->is_limited) {
                $availableStock = (int) ($product->stock_quantity ?? 0);
                if ($availableStock < $quantity) {
                    return response()->json(['message' => 'Insufficient stock quantity.'], 422);
                }
            }

            $existing = OrderItem::query()
                ->where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->quantity += $quantity;
                $existing->calculateTotal();
                $existing->save();
            } else {
                $item = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'notes' => $validated['notes'] ?? null,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'type' => 'product',
                ]);
                $item->calculateTotal();
                $item->save();
            }

            if ($product->is_limited) {
                $product->stock_quantity = max(0, (int) ($product->stock_quantity ?? 0) - $quantity);
                $product->save();
            }

            $order->refresh();
            $order->recalculateTotal();

            return new OrderResource($order->load([
                'user:id,name',
                'unit:id,name',
                'items.product.translations',
                'items.product.media',
            ]));
        });
    }

    public function destroyItem(Order $order, string $item)
    {
        $this->ensureCan('order edit');

        if (! $order->isOpen()) {
            return response()->json(['message' => 'Order is not active.'], 422);
        }

        $orderItem = OrderItem::where('order_id', $order->id)->where('id', $item)->first();
        if (! $orderItem) {
            return response()->json(['message' => 'Line item not found.'], 404);
        }

        return DB::transaction(function () use ($order, $orderItem) {
            if ($orderItem->product_id) {
                $product = Product::query()->lockForUpdate()->find($orderItem->product_id);
                if ($product && $product->is_limited) {
                    $restoreQty = max(0, (int) ($orderItem->quantity ?? 0));
                    $currentStock = max(0, (int) ($product->stock_quantity ?? 0));
                    $product->stock_quantity = $currentStock + $restoreQty;
                    $product->save();
                }
            }

            $orderItem->delete();
            $order->refresh();
            $order->recalculateTotal();

            return new OrderResource($order->load([
                'user:id,name',
                'unit:id,name',
                'items.product.translations',
                'items.product.media',
            ]));
        });
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
