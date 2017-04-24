<?php

namespace BrianFaust\Laravel\Basket;

use Illuminate\Events\Dispatcher;
use BrianFaust\Basket\Contracts\Jurisdiction;
use BrianFaust\Laravel\Basket\Storage\StorageInterface;

class BasketManager
{
    /**
     * The storage driver used by Basket.
     *
     * @var \BrianFaust\Laravel\Basket\Storage\StorageInterface
     */
    protected $storage;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * The basket factory instance.
     *
     * @var \BrianFaust\Laravel\Basket\BasketFactory
     */
    protected $basket;

    /**
     * The basket jurisdiction.
     *
     * @var \BrianFaust\Basket\Contracts\Jurisdiction
     */
    protected $jurisdiction;

    /**
     * Constructor.
     *
     * @param \BrianFaust\Laravel\Basket\StorageInterface $storage
     * @param \Illuminate\Events\Dispatcher               $dispatcher
     */
    public function __construct(StorageInterface $storage, Dispatcher $dispatcher, Jurisdiction $jurisdiction)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
        $this->jurisdiction = $jurisdiction;
    }

    /**
     * Returns the Basket instance identifier.
     *
     * @return mixed
     */
    public function getInstance()
    {
        return $this->storage->getInstance();
    }

    /**
     * Sets the Basket instance identifier.
     *
     * @param mixed $instance
     */
    public function setInstance($instance)
    {
        $this->storage->setInstance($instance);
    }

    /**
     * Returns the storage driver.
     *
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Sets the storage driver.
     *
     * @param \BrianFaust\Laravel\Basket\Storage\StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Returns the event dispatcher instance.
     *
     * @return \Illuminate\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Sets the event dispatcher instance.
     *
     * @param \Illuminate\Events\Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns the Basket jurisdiction.
     *
     * @return \BrianFaust\Basket\Contracts\Jurisdiction
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * Sets the Basket jurisdiction.
     *
     * @param \BrianFaust\Basket\Contracts\Jurisdiction $jurisdiction
     */
    public function setJurisdiction(Jurisdiction $jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
    }

    /**
     * Fires an event.
     *
     * @param string $event
     * @param mixed  $data
     */
    public function fire($event, $data)
    {
        $this->dispatcher->fire("faustbrian.basket.{$event}", $data);
    }

    /**
     * Handle dynamic calls into BasketFactory.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->getFactory(), $method], $parameters);
    }

    /**
     * Returns the basket contents.
     *
     * @return \BrianFaust\Laravel\Basket\BasketFactory
     */
    private function getFactory()
    {
        // return the current instance
        if ($this->basket) {
            return $this->basket;
        }

        // return the basket from session storage
        if ($this->storage->has()) {
            $this->basket = $this->storage->get()->setManager($this);

            $this->fire('restored', $this->basket);

            return $this->basket;
        }

        // create a new basket
        if (!$this->storage->has()) {
            $this->basket = (new BasketFactory())->setManager($this);

            $this->storage->put($this->basket);
            $this->storage->save();

            $this->fire('created', $this->basket);

            return $this->basket;
        }
    }
}
