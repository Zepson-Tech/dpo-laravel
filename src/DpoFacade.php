<?php

namespace Zepson\Dpo;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Zepson\Dpo\Dpo
 * 
 */
class DpoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dpo-laravel';
    }
}
