<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Models\UnitGroup;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'unit_group_id',
        'name',
        'capacity',
        'type',
        'color',
        'active',
        'properties',
        'status',
        'reserved_at',
        'reserved_by',
        'current_order_id',
        'position',
        'fee_per_hour',
    ];

    protected $casts = [
        'type' => UnitType::class,
        'active' => 'boolean',
        'properties' => 'array',
        'reserved_at' => 'datetime',
        'fee_per_hour' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function group()
    {
        return $this->belongsTo(UnitGroup::class, 'unit_group_id');
    }


    public function currentOrder()
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (IMPORTANT for clean code)
    |--------------------------------------------------------------------------
    */

    public function isFree(): bool
    {
        return is_null($this->current_order_id);
    }

    public function isBusy(): bool
    {
        return !is_null($this->current_order_id);
    }

    public function isTable(): bool
    {
        return $this->type === UnitType::Table;
    }

    public function isRoom(): bool
    {
        return $this->type === UnitType::Room;
    }

    /**
     * Total fee for a duration in hours (e.g. order billing).
     */
    public function feeForHours(float $hours): float
    {
        return round((float) $this->fee_per_hour * $hours, 2);
    }
}
