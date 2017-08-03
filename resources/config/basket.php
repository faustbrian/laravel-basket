<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Key
    |--------------------------------------------------------------------------
    |
    | This option allows you to specify the default session key used by the Basket.
    |
    */

    'session_key' => 'faustbrian_basket',

    /*
    |--------------------------------------------------------------------------
    | Default Basket Instance
    |--------------------------------------------------------------------------
    |
    | Define here the name of the default basket instance.
    |
    */

    'instance' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Default Basket Jurisdiction
    |--------------------------------------------------------------------------
    |
    | Define here the class of the default basket jurisdiction.
    |
    */

    'jurisdiction' => BrianFaust\Basket\Jurisdictions\Europe\Germany::class,

];
