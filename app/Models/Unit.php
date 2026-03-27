<?php

namespace App\Models;

use App\Models\UnitGroups;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'unit_group_id',
        'name',
        'capacity',
        'is_active',
        'is_available',
        'current_order_id',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function group()
    {
        return $this->belongsTo(UnitGroups::class, 'unit_group_id');
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
}
