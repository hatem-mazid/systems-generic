<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'unit_id',
        'user_id',
        'status',
        'total',
        'reserved_at',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'reserved_at' => 'datetime',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function timerSessions()
    {
        return $this->hasMany(TimerSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isOpen(): bool
    {
        return in_array($this->status, ['active', 'open', 'ordering'], true);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items()->sum('total');
        $this->save();
    }
}
