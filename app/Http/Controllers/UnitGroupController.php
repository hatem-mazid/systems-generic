<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitGroupCollection;
use App\Models\UnitGroup;
use Illuminate\Http\Request;

class UnitGroupController extends Controller
{
    function index(Request $request)
    {
        $unitGroups = UnitGroup::with('units')
            ->orderBy('position')
            ->paginate($request->integer('per_page', 10));

        return new UnitGroupCollection($unitGroups);
    }
}
