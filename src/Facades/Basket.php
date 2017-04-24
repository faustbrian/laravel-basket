<?php

namespace BrianFaust\Laravel\Basket\Facades;

use Illuminate\Support\Facades\Facade;

class Basket extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'basket';
    }
}
