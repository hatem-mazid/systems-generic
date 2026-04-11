<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseType;
use App\Http\Resources\ExpenseCollection;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Minimal user list for expense "expense by" dropdown (avoids requiring users index).
     */
    public function userOptions(Request $request)
    {
        $this->ensureCanAny(['expenses create', 'expenses edit']);

        $users = User::query()
            ->where('active', true)
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name']);

        return response()->json([
            'items' => $users->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
            ])->values()->all(),
        ]);
    }

    public function index(Request $request)
    {
        $this->ensureCan('expenses index');

        $query = Expense::query()
            ->with(['expenseBy', 'createdBy'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date('date_to'));
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return new ExpenseCollection($query->paginate($perPage));
    }

    public function show(string $id)
    {
        $this->ensureCan('expenses edit');

        $expense = Expense::with(['expenseBy', 'createdBy'])->find($id);
        if (! $expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        return response()->json(new ExpenseResource($expense));
    }

    public function store(Request $request)
    {
        $this->ensureCan('expenses create');

        $validated = $request->validate([
            'description' => 'required|string|max:65535',
            'amount' => 'required|numeric|min:0',
            'type' => ['required', 'string', Rule::enum(ExpenseType::class)],
            'expense_date' => 'required|date',
            'expense_by_id' => 'nullable|exists:users,id',
        ]);

        $validated['created_by_id'] = auth()->id();

        $expense = Expense::create($validated);
        $expense->load(['expenseBy', 'createdBy']);

        return response()->json(new ExpenseResource($expense));
    }

    public function update(Request $request, string $id)
    {
        $this->ensureCan('expenses edit');

        $expense = Expense::find($id);
        if (! $expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:65535',
            'amount' => 'sometimes|required|numeric|min:0',
            'type' => ['sometimes', 'required', 'string', Rule::enum(ExpenseType::class)],
            'expense_date' => 'sometimes|required|date',
            'expense_by_id' => 'nullable|exists:users,id',
        ]);

        $expense->update($validated);
        $expense->load(['expenseBy', 'createdBy']);

        return response()->json(new ExpenseResource($expense));
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function ensureCanAny(array $permissions): void
    {
        $user = auth()->user();
        abort_unless(
            $user && collect($permissions)->contains(fn (string $p) => $user->can($p)),
            403,
            'Forbidden'
        );
    }
}
