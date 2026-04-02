<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitResource;
use App\Enums\UnitType;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'fee_per_hour' => 'nullable|numeric|min:0|max:99999999.99',
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
            'fee_per_hour' => 'nullable|numeric|min:0|max:99999999.99',
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

    public function startOrder(string $id)
    {
        // TODO: Implement startOrder method.
    }

    public function reserve(string $id)
    {
        // TODO: Implement reserve method.
    }

    public function close(string $id)
    {
        // TODO: Implement close method.
    }

    public function cancelReservation(string $id)
    {
        // TODO: Implement cancelReservation method.
    }
}
