<?php

namespace SimpleCMS\Payment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Payment\Packages\Payment
 */
class Payment extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'payment';
    }
}
