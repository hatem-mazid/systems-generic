<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'amount',
        'spent_at',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'spent_at' => 'datetime',
    ];

}
