<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Laravel\Basket;

use BrianFaust\Basket\Basket;
use BrianFaust\Basket\Collection;
use BrianFaust\Basket\Contracts\Discount;
use BrianFaust\Basket\Contracts\Jurisdiction;
use BrianFaust\Basket\Converter;
use BrianFaust\Basket\MetaData\DeliveryMetaData;
use BrianFaust\Basket\MetaData\DiscountMetaData;
use BrianFaust\Basket\MetaData\ProductsMetaData;
use BrianFaust\Basket\MetaData\SubtotalMetaData;
use BrianFaust\Basket\MetaData\TaxableMetaData;
use BrianFaust\Basket\MetaData\TaxMetaData;
use BrianFaust\Basket\MetaData\TotalMetaData;
use BrianFaust\Basket\MetaData\ValueMetaData;
use BrianFaust\Basket\Order;
use BrianFaust\Basket\Processor;
use BrianFaust\Basket\Product;
use BrianFaust\Basket\Reconcilers\DefaultReconciler;
use BrianFaust\Basket\Transformers\ArrayTransformer;
use Closure;
use Money\Money;

class BasketFactory
{
    /**
     * @var
     */
    private $manager;

    /**
     * @var
     */
    private $basket;

    /**
     * @var
     */
    private $order;

    /**
     * @var
     */
    private $rawOrder;

    /**
     * @var
     */
    private $jurisdiction;

    /**
     * @param $sku
     */
    public function increment($sku)
    {
        $this->update($sku, function ($product) {
            $product->increment();
        });

        $this->reconcile();
    }

    /**
     * @param $sku
     */
    public function decrement($sku)
    {
        $this->update($sku, function ($product) {
            $product->decrement();
        });

        $this->reconcile();
    }

    /**
     * @param $sku
     * @param $quantity
     */
    public function quantity($sku, $quantity)
    {
        $this->update($sku, function ($product) use ($quantity) {
            $product->quantity($quantity);
        });

        $this->reconcile();
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->order['delivery'];
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->order['discount'];
    }

    /**
     * @return mixed
     */
    public function getProductsCount()
    {
        return $this->order['products_count'];
    }

    /**
     * @return mixed
     */
    public function getSubtotal()
    {
        return $this->order['subtotal'];
    }

    /**
     * @return mixed
     */
    public function getTaxable()
    {
        return $this->order['taxable'];
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->order['tax'];
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->order['total'];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->order['value'];
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->order['products'];
    }

    /**
     * @param $sku
     * @param $name
     * @param $price
     * @param array $actions
     */
    public function addQuick($sku, $name, $price, $actions = [])
    {
        $price = new Money($price, $this->jurisdiction->currency());

        $product = new Product($sku, $name, $price, $this->basket->rate());
        $product->action($this->productClosure($actions));

        $this->basket->add($product);

        $this->reconcile();
    }

    /**
     * @param $sku
     * @param array $actions
     */
    public function updateQuick($sku, $actions = [])
    {
        $this->basket->update($sku, $this->productClosure($actions));

        $this->reconcile();
    }

    /**
     * @param Jurisdiction $jurisdiction
     */
    public function setJurisdiction(Jurisdiction $jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
    }

    /**
     * @param Basket $basket
     */
    public function setBasket(Basket $basket)
    {
        $this->basket = $basket;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array $order
     */
    public function setOrder(?array $order)
    {
        $this->order = $order;
    }

    /**
     * @param array $rawOrder
     */
    public function setRawOrder(?Order $rawOrder)
    {
        $this->rawOrder = $rawOrder;
    }

    /**
     * Sets the basket instance.
     *
     * @param \BrianFaust\Laravel\Basket\Basket $basket
     *
     * @return $this
     */
    public function setManager(BasketManager $manager)
    {
        $this->manager = $manager;

        if ($this->manager->getStorage()->has()) {
            $this->setBasket($this->basket);
            $this->setOrder($this->order);
            $this->setRawOrder($this->rawOrder);
            $this->setJurisdiction($this->jurisdiction);
        } else {
            $this->setBasket(new Basket($manager->getJurisdiction()));
            $this->setJurisdiction($manager->getJurisdiction());
        }

        $this->reconcile();

        return $this;
    }

    /**
     * Handle dynamic calls into Basket.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array([$this->basket, $method], $parameters);

        $this->reconcile();

        return $result;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['basket', 'order', 'rawOrder', 'jurisdiction'];
    }

    /**
     * @return mixed
     */
    private function reconcile()
    {
        $reconciler = new DefaultReconciler();

        $processor = new Processor($reconciler, [
            new DeliveryMetaData($reconciler),
            new DiscountMetaData($reconciler),
            new ProductsMetaData(),
            new SubtotalMetaData($reconciler),
            new TaxableMetaData(),
            new TaxMetaData($reconciler),
            new TotalMetaData($reconciler),
            new ValueMetaData($reconciler),
        ]);

        $transformer = new ArrayTransformer(new Converter());

        $this->setRawOrder($order = $processor->process($this->basket));

        $this->setOrder($transformer->transform($order));

        $this->commit();
    }

    /**
     * @param $actions
     *
     * @return Closure
     */
    private function productClosure($actions)
    {
        $currency = $this->jurisdiction->currency();

        return function ($product) use ($currency, $actions) {
            foreach ($actions as $key => $value) {
                if ($key === 'tags') {
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    $value = new Collection($value);
                } elseif ($key === 'delivery') {
                    $value = new Money($value, $currency);
                }

                $product->{$key}($value);
            }
        };
    }

    private function commit()
    {
        $storage = $this->manager->getStorage();
        $storage->put($this);
        $storage->save();
    }
}
