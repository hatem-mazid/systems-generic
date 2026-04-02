<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesJsonRequest;

abstract class Controller
{
    use ValidatesJsonRequest;
}

