<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@basecode.sh>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('basket')) {
    function basket($instance = null)
    {
        $basket = app('basket');

        if ($instance) {
            $basket->setInstance($instance);
        }

        return $basket;
    }
}
