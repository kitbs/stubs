<?php

use Stub\Stub;
use \Illuminate\Support\Facades\Facade;

class StubFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Stub::class;
    }
}
