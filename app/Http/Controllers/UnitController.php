<?php

namespace App\Http\Controllers;

use App\Enums\UnitType;
use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TimerSession;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'unit_group_id' => 'sometimes|integer|exists:unit_groups,id',
        ]);

        $query = Unit::with(['group', 'currentOrder'])
            ->orderBy('position');

        if ($request->filled('unit_group_id')) {
            $query->where('unit_group_id', $request->integer('unit_group_id'));
        }

        $units = $query->paginate($request->integer('per_page', 10));

        return new UnitCollection($units);
    }

    public function show(string $id)
    {
        $unit = Unit::with(['group', 'currentOrder'])->find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        return response()->json(new UnitResource($unit));
    }

    public function store(Request $request)
    {
        $validated = $this->validateJsonOrFail($request, [
            'unit_group_id' => 'required|integer|exists:unit_groups,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'type' => ['required', Rule::enum(UnitType::class)],
            // 'color' => 'nullable|string|max:20',
            // 'properties' => 'nullable|array',
            'active' => 'required|boolean',
            // 'status' => 'required|in:available,reserved,occupied',
            // 'reserved_at' => 'nullable|date',
            // 'reserved_by' => 'nullable|string|max:255',
            // 'current_order_id' => 'nullable|integer',
            'position' => 'nullable|integer',
            'price_per_hour' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $unit = Unit::create($validated);

        return response()->json(new UnitResource($unit->load(['group', 'currentOrder'])));
    }

    public function update(Request $request, string $id)
    {
        $validated = $this->validateJsonOrFail($request, [
            'unit_group_id' => 'sometimes|required|integer|exists:unit_groups,id',
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'type' => ['sometimes', Rule::enum(UnitType::class)],
            // 'color' => 'nullable|string|max:20',
            // 'properties' => 'nullable|array',
            'active' => 'sometimes|boolean',
            // 'status' => 'sometimes|in:available,reserved,occupied',
            // 'reserved_at' => 'nullable|date',
            // 'reserved_by' => 'nullable|string|max:255',
            // 'current_order_id' => 'nullable|integer',
            'position' => 'nullable|integer',
            'price_per_hour' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $unit = Unit::find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        if (! empty($validated)) {
            $unit->update($validated);
        }

        return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
    }

    public function destroy(string $id)
    {
        $unit = Unit::find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete();

        return response()->noContent();
    }

    public function startOrder(Request $request, string $id)
    {
        $unit = Unit::with('currentOrder')->find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        return DB::transaction(function () use ($request, $unit) {
            $unit->refresh()->load('currentOrder');
            $status = strtolower((string) ($unit->status ?? ''));

            if ($status === 'reserved' && $unit->currentOrder && in_array($unit->currentOrder->status, ['reserved', 'pending'], true)) {
                $unit->currentOrder->update([
                    'status' => 'active',
                    'opened_at' => now(),
                    'reserved_at' => null,
                ]);

                $unit->update([
                    'status' => 'occupied',
                    'reserved_at' => null,
                    'reserved_by' => null,
                ]);

                $order = $unit->fresh()->currentOrder;
                if ($order) {
                    $this->startUnitTimerSessionIfNeeded($order, $unit->fresh());
                }

                return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
            }

            if ($status !== 'available') {
                return response()->json(['message' => 'Unit is not available to start an order.'], 422);
            }

            if (! $unit->active) {
                return response()->json(['message' => 'Unit is inactive.'], 422);
            }

            if ($unit->current_order_id && $unit->currentOrder) {
                if (in_array($unit->currentOrder->status, ['closed', 'cancelled'], true)) {
                    $unit->update(['current_order_id' => null]);
                    $unit->refresh()->load('currentOrder');
                } else {
                    return response()->json(['message' => 'Unit already has an active order.'], 422);
                }
            }

            $order = Order::create([
                'unit_id' => $unit->id,
                'user_id' => $request->user()?->id,
                'status' => 'active',
                'total' => 0,
                'reserved_at' => null,
                'opened_at' => now(),
                'closed_at' => null,
            ]);

            $unit->update([
                'status' => 'occupied',
                'current_order_id' => $order->id,
                'reserved_at' => null,
                'reserved_by' => null,
            ]);

            $this->startUnitTimerSessionIfNeeded($order->fresh(), $unit->fresh());

            return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
        });
    }

    public function reserve(Request $request, string $id)
    {
        $validated = $this->validateJsonOrFail($request, [
            'reserved_at' => 'sometimes|nullable|date',
            'reserved_by' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $unit = Unit::with('currentOrder')->find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        if ($unit->type !== UnitType::Table) {
            return response()->json(['message' => 'Only table units can be reserved.'], 422);
        }

        $unitStatus = strtolower((string) ($unit->status ?? ''));
        if (! in_array($unitStatus, ['available', 'reserved'], true)) {
            return response()->json(['message' => 'Unit is not available for reservation.'], 422);
        }

        if (! $unit->active) {
            return response()->json(['message' => 'Unit is inactive.'], 422);
        }

        if ($unit->current_order_id && $unit->currentOrder) {
            if (
                $unitStatus === 'reserved' &&
                in_array($unit->currentOrder->status, ['reserved', 'pending'], true)
            ) {
                // Allow rescheduling an existing reservation.
            } elseif (! in_array($unit->currentOrder->status, ['closed', 'cancelled'], true)) {
                return response()->json(['message' => 'Unit already has an active order.'], 422);
            }
        }

        $reservedAt = isset($validated['reserved_at']) && $validated['reserved_at'] !== null
            ? Carbon::parse($validated['reserved_at'])
            : now();

        return DB::transaction(function () use ($request, $unit, $validated, $reservedAt, $unitStatus) {
            $unit->refresh()->load('currentOrder');

            if (
                $unitStatus === 'reserved' &&
                $unit->currentOrder &&
                in_array($unit->currentOrder->status, ['reserved', 'pending'], true)
            ) {
                $unit->currentOrder->update([
                    'reserved_at' => $reservedAt,
                ]);

                $unit->update([
                    'status' => 'reserved',
                    'reserved_at' => $reservedAt,
                    'reserved_by' => $validated['reserved_by'] ?? null,
                ]);

                return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
            }

            if ($unit->current_order_id && $unit->currentOrder) {
                if (! in_array($unit->currentOrder->status, ['closed', 'cancelled'], true)) {
                    return response()->json(['message' => 'Unit already has an active order.'], 422);
                }
                $unit->update(['current_order_id' => null]);
                $unit->refresh();
            }

            $order = Order::create([
                'unit_id' => $unit->id,
                'user_id' => $request->user()?->id,
                'status' => 'reserved',
                'total' => 0,
                'reserved_at' => $reservedAt,
                'opened_at' => null,
                'closed_at' => null,
            ]);

            $unit->update([
                'status' => 'reserved',
                'reserved_at' => $reservedAt,
                'reserved_by' => $validated['reserved_by'] ?? null,
                'current_order_id' => $order->id,
            ]);

            return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
        });
    }

    public function close(string $id)
    {
        $unit = Unit::with('currentOrder')->find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        if (! $unit->current_order_id || ! $unit->currentOrder) {
            return response()->json(['message' => 'No current order on this unit.'], 422);
        }

        $order = $unit->currentOrder;
        if (in_array($order->status, ['closed', 'cancelled'], true)) {
            return response()->json(['message' => 'Order is already finished.'], 422);
        }

        return DB::transaction(function () use ($unit, $order) {
            $this->finalizeOpenUnitTimerSessionAndAddFees($order, $unit);

            $order->refresh();
            $order->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $unit->update([
                'status' => 'available',
                'current_order_id' => null,
                'reserved_at' => null,
                'reserved_by' => null,
            ]);

            return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
        });
    }

    public function cancelReservation(string $id)
    {
        $unit = Unit::with('currentOrder')->find($id);
        if (! $unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        if (strtolower((string) ($unit->status ?? '')) !== 'reserved') {
            return response()->json(['message' => 'Unit is not reserved.'], 422);
        }

        if (! $unit->currentOrder || ! in_array($unit->currentOrder->status, ['reserved', 'pending'], true)) {
            return response()->json(['message' => 'No reserved order on this unit.'], 422);
        }

        return DB::transaction(function () use ($unit) {
            $order = $unit->currentOrder;
            $order->update([
                'status' => 'cancelled',
                'closed_at' => now(),
            ]);

            $unit->update([
                'status' => 'available',
                'current_order_id' => null,
                'reserved_at' => null,
                'reserved_by' => null,
            ]);

            return response()->json(new UnitResource($unit->fresh()->load(['group', 'currentOrder'])));
        });
    }

    public function transferGuests(Request $request, string $id)
    {
        $validated = $this->validateJsonOrFail($request, [
            'target_unit_id' => 'required|integer|exists:units,id',
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $sourceUnit = Unit::with('currentOrder')->find($id);
        if (! $sourceUnit) {
            return response()->json(['message' => 'Source unit not found'], 404);
        }

        $targetId = (int) $validated['target_unit_id'];
        if ((int) $sourceUnit->id === $targetId) {
            return response()->json(['message' => 'Target unit must be different from source unit.'], 422);
        }

        $targetUnit = Unit::with('currentOrder')->find($targetId);
        if (! $targetUnit) {
            return response()->json(['message' => 'Target unit not found'], 404);
        }

        if (strtolower((string) $sourceUnit->status) !== 'occupied') {
            return response()->json(['message' => 'Source unit is not occupied.'], 422);
        }

        $sourceOrder = $sourceUnit->currentOrder;
        if (! $sourceOrder || in_array($sourceOrder->status, ['closed', 'cancelled', 'reserved', 'pending'], true)) {
            return response()->json(['message' => 'Source unit has no active order to transfer.'], 422);
        }

        if (strtolower((string) $targetUnit->status) !== 'available' || ! $targetUnit->active) {
            return response()->json(['message' => 'Target unit must be active and available.'], 422);
        }

        if ($targetUnit->current_order_id && $targetUnit->currentOrder) {
            if (! in_array($targetUnit->currentOrder->status, ['closed', 'cancelled'], true)) {
                return response()->json(['message' => 'Target unit already has an active order.'], 422);
            }
        }

        return DB::transaction(function () use ($sourceUnit, $targetUnit) {
            $sourceLocked = Unit::whereKey($sourceUnit->id)->lockForUpdate()->first();
            $targetLocked = Unit::whereKey($targetUnit->id)->lockForUpdate()->first();

            if (! $sourceLocked || ! $targetLocked) {
                return response()->json(['message' => 'Unit not found during transfer.'], 404);
            }

            $sourceLocked->load('currentOrder');
            $targetLocked->load('currentOrder');

            if (strtolower((string) $sourceLocked->status) !== 'occupied') {
                return response()->json(['message' => 'Source unit is not occupied.'], 422);
            }

            if (strtolower((string) $targetLocked->status) !== 'available' || ! $targetLocked->active) {
                return response()->json(['message' => 'Target unit must be active and available.'], 422);
            }

            $order = $sourceLocked->currentOrder;
            if (! $order || in_array($order->status, ['closed', 'cancelled', 'reserved', 'pending'], true)) {
                return response()->json(['message' => 'Source unit has no active order to transfer.'], 422);
            }

            if ($targetLocked->current_order_id && $targetLocked->currentOrder) {
                if (! in_array($targetLocked->currentOrder->status, ['closed', 'cancelled'], true)) {
                    return response()->json(['message' => 'Target unit already has an active order.'], 422);
                }
                $targetLocked->update(['current_order_id' => null]);
            }

            $order->update([
                'unit_id' => $targetLocked->id,
            ]);

            $sourceLocked->update([
                'status' => 'available',
                'current_order_id' => null,
                'reserved_at' => null,
                'reserved_by' => null,
            ]);

            $targetLocked->update([
                'status' => 'occupied',
                'current_order_id' => $order->id,
                'reserved_at' => null,
                'reserved_by' => null,
            ]);

            return response()->json([
                'source_unit' => new UnitResource($sourceLocked->fresh()->load(['group', 'currentOrder'])),
                'target_unit' => new UnitResource($targetLocked->fresh()->load(['group', 'currentOrder'])),
            ]);
        });
    }

    /**
     * When an order becomes active, track billable time for units with hourly pricing.
     */
    private function startUnitTimerSessionIfNeeded(Order $order, Unit $unit): void
    {
        if (! $this->unitHasHourlyRate($unit)) {
            return;
        }

        $hasOpen = TimerSession::query()
            ->where('order_id', $order->id)
            ->where('unit_id', $unit->id)
            ->whereNull('end_time')
            ->exists();

        if ($hasOpen) {
            return;
        }

        $start = $order->opened_at ?? now();

        TimerSession::create([
            'order_id' => $order->id,
            'unit_id' => $unit->id,
            'product_id' => null,
            'start_time' => $start,
            'end_time' => null,
            'duration' => null,
            'price_per_hour_snapshot' => $unit->price_per_hour,
            'total_price' => null,
        ]);
    }

    private function unitHasHourlyRate(Unit $unit): bool
    {
        return (float) ($unit->price_per_hour ?? 0) > 0;
    }

    /**
     * Close the open unit timer session and add a unitFees line item from elapsed time.
     */
    private function finalizeOpenUnitTimerSessionAndAddFees(Order $order, Unit $unit): void
    {
        $session = TimerSession::query()
            ->where('order_id', $order->id)
            ->whereNull('end_time')
            ->whereNotNull('unit_id')
            ->with('unit')
            ->first();

        if (! $session) {
            return;
        }

        $end = now();
        $start = $session->start_time;
        $seconds = max(0, (int) $start->diffInSeconds($end));
        $hours = $seconds / 3600.0;
        $totalPrice = round((float) $session->price_per_hour_snapshot * $hours, 2);
        $minutes = (int) max(0, round($seconds / 60));

        $session->update([
            'end_time' => $end,
            'duration' => $minutes,
            'total_price' => $totalPrice,
        ]);

        if ($totalPrice <= 0) {
            $order->recalculateTotal();

            return;
        }

        $labelUnit = $session->unit ?? $unit;
        $name = $labelUnit->name
            ? sprintf('Unit fee (%s)', $labelUnit->name)
            : 'Unit fee';

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null,
            'name' => $name,
            'notes' => null,
            'price' => $totalPrice,
            'quantity' => 1,
            'total' => $totalPrice,
            'type' => 'unitFees',
            'meta' => [
                'timer_session_id' => $session->id,
                'duration_minutes' => $minutes,
                'duration_seconds' => $seconds,
                'price_per_hour' => (string) $session->price_per_hour_snapshot,
            ],
        ]);

        $order->recalculateTotal();
    }
}
