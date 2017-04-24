<?php

if (!function_exists('basket')) {
    function basket($instance = null)
    {
        if ($instance) {
            Basket::setInstance($instance);
        }

        return app('basket');
    }
}
