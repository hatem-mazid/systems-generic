<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TimerSession;
use App\Models\Unit;

class OrderUnitBillingService
{
    /**
     * When an order becomes active, track billable time for units with hourly pricing.
     */
    public function startTimerSessionIfNeeded(Order $order, Unit $unit): void
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

    /**
     * Close the open unit timer session and add a unitFees line item from elapsed time.
     */
    public function finalizeOpenUnitTimerSessionAndAddFees(Order $order, Unit $unit): void
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
            ? __('messages.unit_fee_with_name', ['name' => $labelUnit->name])
            : __('messages.unit_fee');

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
                'unit_name' => $labelUnit->name,
                'duration_minutes' => $minutes,
                'duration_seconds' => $seconds,
                'price_per_hour' => (string) $session->price_per_hour_snapshot,
            ],
        ]);

        $order->recalculateTotal();
    }

    private function unitHasHourlyRate(Unit $unit): bool
    {
        return (float) ($unit->price_per_hour ?? 0) > 0;
    }
}
