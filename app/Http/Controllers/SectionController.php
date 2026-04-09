<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Active sections for product assignment (ordered for UI).
     */
    public function index(Request $request)
    {
        $this->ensureCan('products index');

        $items = Section::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'items' => $items,
        ]);
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
