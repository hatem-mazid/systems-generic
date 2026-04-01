<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        // $request->validate([
        //     'per_page' => 'sometimes|integer|min:1|max:100',
        //     'unit_group_id' => 'sometimes|integer|exists:unit_groups,id',
        // ]);

        // $query = Unit::with(['group', 'currentOrder'])
        //     ->orderBy('position');

        // if ($request->filled('unit_group_id')) {
        //     $query->where('unit_group_id', $request->integer('unit_group_id'));
        // }

        // $units = $query->paginate($request->integer('per_page', 10));

        // return new UnitCollection($units);
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
        $validated = $request->validate([
            'unit_group_id' => 'required|integer|exists:unit_groups,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'properties' => 'nullable|array',
            'is_active' => 'required|boolean',
            'is_available' => 'nullable|boolean',
            'reserved_at' => 'nullable|date',
            'current_order_id' => 'nullable|integer',
            'position' => 'nullable|integer',
        ]);

        $unit = Unit::create($validated);

        return response()->json(new UnitResource($unit->load(['group', 'currentOrder'])));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'unit_group_id' => 'sometimes|required|integer|exists:unit_groups,id',
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'properties' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
            'reserved_at' => 'nullable|date',
            'current_order_id' => 'nullable|integer',
            'position' => 'nullable|integer',
        ]);

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
}
