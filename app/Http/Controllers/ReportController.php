<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Aggregated closed-order totals and order counts by calendar period.
     */
    public function orders(Request $request)
    {
        $this->ensureCan('view reports');

        $validated = $request->validate([
            'group_by' => 'sometimes|string|in:day,week,month',
            'date_from' => 'sometimes|nullable|date',
            'date_to' => 'sometimes|nullable|date|after_or_equal:date_from',
        ]);

        $groupBy = $validated['group_by'] ?? 'day';

        $dateTo = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : Carbon::now()->endOfDay();
        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : (clone $dateTo)->subMonthsNoOverflow(3)->startOfDay();

        $periodSql = $this->periodExpression('orders.closed_at', $groupBy);

        $rows = Order::query()
            ->where('status', 'closed')
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$dateFrom, $dateTo])
            ->selectRaw("{$periodSql} as period")
            ->selectRaw('COALESCE(SUM(orders.total), 0) as total_value')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $series = $rows->map(function ($row) {
            return [
                'period' => (string) $row->period,
                'total_value' => round((float) $row->total_value, 2),
                'order_count' => (int) $row->order_count,
            ];
        })->all();

        $unitGroupBreakdownRows = Order::query()
            ->leftJoin('units', 'orders.unit_id', '=', 'units.id')
            ->leftJoin('unit_groups', 'units.unit_group_id', '=', 'unit_groups.id')
            ->whereIn('orders.status', ['closed', 'takeaway'])
            ->whereRaw(
                'COALESCE(orders.closed_at, orders.opened_at, orders.created_at) BETWEEN ? AND ?',
                [$dateFrom, $dateTo]
            )
            ->selectRaw('unit_groups.id as unit_group_id')
            ->selectRaw('CASE WHEN orders.unit_id IS NULL THEN 1 ELSE 0 END as is_takeaway')
            ->selectRaw("CASE WHEN orders.unit_id IS NULL THEN 'takeaway' ELSE COALESCE(unit_groups.name, 'Unassigned group') END as group_name")
            ->selectRaw('SUM(orders.total) as total_value')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->groupBy('unit_group_id', 'is_takeaway', 'group_name')
            ->orderBy('is_takeaway')
            ->orderBy('group_name')
            ->get();

        $unitGroupBreakdown = $unitGroupBreakdownRows->map(function ($row) {
            return [
                'unit_group_id' => $row->unit_group_id !== null ? (int) $row->unit_group_id : null,
                'group_name' => (string) $row->group_name,
                'is_takeaway' => (int) $row->is_takeaway === 1,
                'total_value' => round((float) $row->total_value, 2),
                'order_count' => (int) $row->order_count,
            ];
        })->all();

        return response()->json([
            'group_by' => $groupBy,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'series' => $series,
            'unit_group_breakdown' => $unitGroupBreakdown,
        ]);
    }

    /**
     * Aggregated expense amounts and counts by calendar period.
     */
    public function expenses(Request $request)
    {
        $this->ensureCan('view reports');

        $validated = $request->validate([
            'group_by' => 'sometimes|string|in:day,week,month',
            'date_from' => 'sometimes|nullable|date',
            'date_to' => 'sometimes|nullable|date|after_or_equal:date_from',
        ]);

        $groupBy = $validated['group_by'] ?? 'day';

        $dateTo = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : Carbon::now()->endOfDay();
        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : (clone $dateTo)->subMonthsNoOverflow(3)->startOfDay();

        $periodSql = $this->periodExpression('expenses.expense_date', $groupBy);

        $rows = Expense::query()
            ->whereBetween('expense_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->selectRaw("{$periodSql} as period")
            ->selectRaw('COALESCE(SUM(expenses.amount), 0) as total_amount')
            ->selectRaw('COUNT(expenses.id) as expense_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $series = $rows->map(function ($row) {
            return [
                'period' => (string) $row->period,
                'total_amount' => round((float) $row->total_amount, 2),
                'expense_count' => (int) $row->expense_count,
            ];
        })->all();

        return response()->json([
            'group_by' => $groupBy,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'series' => $series,
        ]);
    }

    /**
     * MySQL-compatible period bucket for closed_at.
     */
    private function periodExpression(string $column, string $groupBy): string
    {
        return match ($groupBy) {
            'day' => "DATE({$column})",
            'week' => "DATE(DATE_SUB({$column}, INTERVAL WEEKDAY({$column}) DAY))",
            'month' => "DATE_FORMAT({$column}, '%Y-%m-01')",
            default => "DATE({$column})",
        };
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
