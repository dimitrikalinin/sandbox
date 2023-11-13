<?php

namespace App\Facades;

use \Illuminate\Support\Facades\Facade;

class Zipkin extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'App\Services\ZipkinService';
    }

}