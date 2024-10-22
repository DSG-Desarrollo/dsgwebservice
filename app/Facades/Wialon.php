<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Wialon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wialon';
    }
}
