<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'position',
        'printer_name',
    ];
}
