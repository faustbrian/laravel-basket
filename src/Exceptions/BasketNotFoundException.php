<?php

namespace DraperStudio\LaravelBasket\Exceptions;

use RuntimeException;

class BasketNotFoundException extends RuntimeException
{
    protected $identifier;

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        $this->message = "No basket found for the identifier [{$identifier}].";

        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
