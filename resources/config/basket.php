<?php

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
