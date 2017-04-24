<?php

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('basket')) {
    function basket($instance = null)
    {
        if ($instance) {
            Basket::setInstance($instance);
        }

        return app('basket');
    }
}
