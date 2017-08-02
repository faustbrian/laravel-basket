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

use Illuminate\Session\Store as SessionStore;

class IlluminateSession implements StorageInterface
{
    /**
     * The key used in the Session.
     *
     * @var string
     */
    protected $key = 'faustbrian_basket';

    /**
     * The instance that is being used.
     *
     * @var string
     */
    protected $instance = 'main';

    /**
     * Session store object.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * Creates a new Illuminate based Session driver for Basket.
     *
     * @param \Illuminate\Session\Store $session
     * @param string                    $instance
     * @param string                    $key
     */
    public function __construct(SessionStore $session, $instance = null, $key = null)
    {
        $this->session = $session;
        $this->key = $key ?: $this->key;
        $this->instance = $instance ?: $this->instance;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    public function get()
    {
        return $this->session->get($this->getSessionKey());
    }

    public function put($value)
    {
        $this->session->put($this->getSessionKey(), $value);
    }

    public function has()
    {
        return $this->session->has($this->getSessionKey());
    }

    public function forget()
    {
        $this->session->forget($this->getSessionKey());
    }

    public function save()
    {
        $this->session->save();
    }

    public function flush()
    {
        $this->session->flush();
    }

    /**
     * Returns both session key and session instance.
     *
     * @return string
     */
    protected function getSessionKey()
    {
        return "{$this->getKey()}.{$this->getInstance()}";
    }
}
