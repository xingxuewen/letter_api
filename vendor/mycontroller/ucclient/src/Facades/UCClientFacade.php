<?php

namespace MyController\UCClient\Facades;

use Illuminate\Support\Facades\Facade;

class UCClientFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ucenter.client';
    }
}
