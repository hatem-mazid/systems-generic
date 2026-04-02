<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case ServiceFixed = 'service_fixed';
    case ServiceTimer = 'service_timer';
}
