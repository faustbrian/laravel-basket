<?php

/*
 * This file is part of Laravel Basket.
 *
 * (c) DraperStudio <hello@draperstudio.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraperStudio\LaravelBasket\Exceptions;

use RuntimeException;

/**
 * Class BasketNotFoundException.
 *
 * @author DraperStudio <hello@draperstudio.tech>
 */
class BasketNotFoundException extends RuntimeException
{
    /**
     * @var
     */
    protected $identifier;

    /**
     * @param $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        $this->message = "No basket found for the identifier [{$identifier}].";

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
