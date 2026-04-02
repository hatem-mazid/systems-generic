<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TimerSession extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'unit_id',
        'start_time',
        'end_time',
        'duration',
        'price_per_hour_snapshot',
        'total_price',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'price_per_hour_snapshot' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (TimerSession $session): void {
            $hasProduct = $session->product_id !== null;
            $hasUnit = $session->unit_id !== null;
            if ($hasProduct === $hasUnit) {
                throw new InvalidArgumentException(
                    'TimerSession must have exactly one of product_id or unit_id.'
                );
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
