<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitGroupCollection;
use App\Http\Resources\UnitGroupResource;
use App\Models\UnitGroup;
use Illuminate\Http\Request;

class UnitGroupController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCan('unit-groups index');

        $unitGroups = UnitGroup::with(['units.currentOrder'])
            ->orderBy('position')
            ->paginate($request->integer('per_page', 10));

        return new UnitGroupCollection($unitGroups);
    }

    public function show(string $id)
    {
        $this->ensureCan('unit-groups edit');

        $unitGroup = UnitGroup::with(['units.currentOrder'])->find($id);
        if (! $unitGroup) {
            return response()->json(['message' => 'Unit group not found'], 404);
        }

        return response()->json(new UnitGroupResource($unitGroup));
    }

    public function store(Request $request)
    {
        $this->ensureCan('unit-groups create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'position' => 'nullable|integer',
        ]);

        $unitGroup = UnitGroup::create($validated);

        return response()->json(new UnitGroupResource($unitGroup->load(['units.currentOrder'])));
    }

    public function update(Request $request, string $id)
    {
        $this->ensureCan('unit-groups edit');

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'position' => 'nullable|integer',
        ]);

        $unitGroup = UnitGroup::find($id);
        if (! $unitGroup) {
            return response()->json(['message' => 'Unit group not found'], 404);
        }

        if (! empty($validated)) {
            $unitGroup->update($validated);
        }

        return response()->json(new UnitGroupResource($unitGroup->fresh()->load(['units.currentOrder'])));
    }

    public function destroy(string $id)
    {
        $this->ensureCan('unit-groups delete');

        $unitGroup = UnitGroup::find($id);
        if (! $unitGroup) {
            return response()->json(['message' => 'Unit group not found'], 404);
        }

        $unitGroup->delete();

        return response()->noContent();
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
