<?php

namespace App\Models;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

class UnitGroups extends Model
{
    protected $fillable = [
        'name',
        'color',
        'is_active',
        'position',
    ];


    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
