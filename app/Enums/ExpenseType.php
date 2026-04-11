<?php

namespace App\Enums;

enum ExpenseType: string
{
    case Rent = 'rent';
    case Supplies = 'supplies';
    case Payroll = 'payroll';
    case Utilities = 'utilities';
    case Marketing = 'marketing';
    case Other = 'other';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
