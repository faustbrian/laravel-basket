<?php

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Laravel\Basket\Storage;

interface StorageInterface
{
    /**
     * Returns the session key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Returns the session instance identifier.
     *
     * @return string
     */
    public function getInstance();

    /**
     * Sets the session instance identifier.
     *
     * @param string $instance
     *
     * @return string
     */
    public function setInstance($instance);

    /**
     * Get the value from the storage.
     *
     * @return mixed
     */
    public function get();

    /**
     * Put a value.
     *
     * @param mixed $value
     */
    public function put($value);

    /**
     * Checks if an attribute is defined.
     *
     * @return bool
     */
    public function has();

    /**
     * Remove the storage.
     */
    public function forget();
}
