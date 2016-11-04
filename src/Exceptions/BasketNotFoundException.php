<?php

namespace BrianFaust\LaravelBasket\Exceptions;

use RuntimeException;

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
