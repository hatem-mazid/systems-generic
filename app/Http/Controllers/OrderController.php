<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
            'items.product.section',
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
            'items.product.section',
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

            $notesRaw = $validated['notes'] ?? null;
            $notesNormalized = ($notesRaw === null || $notesRaw === '') ? null : $notesRaw;

            $existingQuery = OrderItem::query()
                ->where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('is_printed', false)
                ->whereNull('batch_no');

            if ($notesNormalized === null) {
                $existingQuery->where(function ($q) {
                    $q->whereNull('notes')->orWhere('notes', '');
                });
            } else {
                $existingQuery->where('notes', $notesNormalized);
            }

            $existing = $existingQuery->lockForUpdate()->first();

            if ($existing) {
                $existing->quantity = (int) ($existing->quantity ?? 0) + $quantity;
                $existing->calculateTotal();
                $existing->save();
            } else {
                $item = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'notes' => $notesNormalized,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'type' => 'product',
                    'batch_no' => null,
                    'is_printed' => false,
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
                'items.product.section',
            ]));
        });
    }

    public function updateItem(Request $request, Order $order, string $item)
    {
        $this->ensureCan('order edit');

        if (! $order->isOpen()) {
            return response()->json(['message' => 'Order is not active.'], 422);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $qtyNew = (int) $validated['quantity'];

        return DB::transaction(function () use ($order, $item, $qtyNew) {
            $orderItem = OrderItem::query()
                ->where('order_id', $order->id)
                ->where('id', $item)
                ->lockForUpdate()
                ->first();

            if (! $orderItem) {
                return response()->json(['message' => 'Line item not found.'], 404);
            }

            if ($orderItem->is_printed) {
                return response()->json(['message' => 'Item is already printed.'], 422);
            }

            if ($orderItem->batch_no !== null) {
                return response()->json(['message' => 'Only open batch lines can be updated.'], 422);
            }

            $qtyOld = (int) ($orderItem->quantity ?? 0);
            $delta = $qtyNew - $qtyOld;

            if ($delta === 0) {
                $order->refresh();

                return new OrderResource($order->load([
                    'user:id,name',
                    'unit:id,name',
                    'items.product.translations',
                    'items.product.media',
                    'items.product.section',
                ]));
            }

            if ($orderItem->product_id) {
                $product = Product::query()->lockForUpdate()->find($orderItem->product_id);
                if ($product && $product->is_limited) {
                    if ($delta > 0) {
                        $available = (int) ($product->stock_quantity ?? 0);
                        if ($available < $delta) {
                            return response()->json(['message' => 'Insufficient stock quantity.'], 422);
                        }
                        $product->stock_quantity = max(0, $available - $delta);
                        $product->save();
                    } else {
                        $product->stock_quantity = max(0, (int) ($product->stock_quantity ?? 0) + (-$delta));
                        $product->save();
                    }
                }
            }

            $orderItem->quantity = $qtyNew;
            $orderItem->calculateTotal();
            $orderItem->save();

            $order->refresh();
            $order->recalculateTotal();

            return new OrderResource($order->load([
                'user:id,name',
                'unit:id,name',
                'items.product.translations',
                'items.product.media',
                'items.product.section',
            ]));
        });
    }

    public function print(Order $order)
    {
        $this->ensureCan('order edit');

        $payload = DB::transaction(function () use ($order) {
            $items = OrderItem::query()
                ->where('order_id', $order->id)
                ->where('is_printed', false)
                ->with(['product.section'])
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return [
                    'order_id' => $order->id,
                    'sections' => [],
                    'patches' => [],
                    'printed_items_count' => 0,
                ];
            }

            $nextBatchNo = ((int) OrderItem::query()
                ->where('order_id', $order->id)
                ->max('batch_no')) + 1;

            $items->each(function (OrderItem $item) use ($nextBatchNo) {
                $item->batch_no = $nextBatchNo;
            });

            $grouped = $this->groupItemsBySectionForPrinting($items);
            $patches = $this->groupPatchesForPrinting($items);

            OrderItem::query()
                ->whereIn('id', $items->pluck('id'))
                ->update([
                    'batch_no' => $nextBatchNo,
                    'is_printed' => true,
                ]);

            return [
                'order_id' => $order->id,
                'sections' => $grouped,
                'patches' => $patches,
                'printed_items_count' => $items->count(),
            ];
        });

        return response()->json($payload);
    }

    public function destroyItem(Order $order, string $item)
    {
        $this->ensureCan('order item delete');

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
                'items.product.section',
            ]));
        });
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }

    private function groupItemsBySectionForPrinting(Collection $items): array
    {
        return $this->buildSectionGroupsForPrinting($items);
    }

    /**
     * @return array<int, array{batch_no: int, sections: array<int, mixed>}>
     */
    private function groupPatchesForPrinting(Collection $items): array
    {
        return $items
            ->groupBy(fn (OrderItem $item) => (int) ($item->batch_no ?? 1))
            ->sortKeys()
            ->map(function (Collection $batchItems, int $batchNo) {
                return [
                    'batch_no' => $batchNo,
                    'sections' => $this->buildSectionGroupsForPrinting($batchItems),
                ];
            })
            ->values()
            ->all();
    }

    private function buildSectionGroupsForPrinting(Collection $items): array
    {
        return $items
            ->groupBy(function (OrderItem $item) {
                return $item->product?->section?->code ?? 'general';
            })
            ->map(function (Collection $sectionItems, string $sectionCode) {
                return [
                    'section_code' => $sectionCode,
                    'section_name' => $sectionItems->first()->product?->section?->name ?? 'General',
                    'printer_name' => $sectionItems->first()->product?->section?->printer_name,
                    'items' => $sectionItems->map(function (OrderItem $item) {
                        return [
                            'id' => $item->id,
                            'batch_no' => $item->batch_no,
                            'name' => $item->name,
                            'notes' => $item->notes,
                            'quantity' => $item->quantity,
                            'type' => $item->type,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
