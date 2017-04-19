<?php

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/*
 * This file is part of Laravel Basket.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\LaravelBasket;

use BrianFaust\Basket\Basket as BaseBasket;
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
use BrianFaust\Basket\Processor;
use BrianFaust\Basket\Product;
use BrianFaust\Basket\Reconcilers\DefaultReconciler;
use BrianFaust\Basket\Transformers\ArrayTransformer;
use BrianFaust\LaravelBasket\Exceptions\BasketNotFoundException;
use Closure;
use Illuminate\Session\SessionManager;
use Money\Money;

class Basket
{
    /**
     * @var string
     */
    private $prefix = 'basket_';

    /**
     * @var
     */
    private $session;

    /**
     * @var
     */
    private $basket;

    /**
     * @var
     */
    private $identifier;

    /**
     * @var
     */
    private $order;

    /**
     * Basket constructor.
     *
     * @param SessionManager $session
     */
    public function __construct(SessionManager $session)
    {
        $this->setSession($session);
    }

    /**
     * @param $identifier
     * @param Jurisdiction $jurisdiction
     *
     * @return $this
     */
    public function setup($identifier, Jurisdiction $jurisdiction)
    {
        $this->setBasket(new BaseBasket($jurisdiction));
        $this->setJurisdiction($jurisdiction);
        $this->setIdentifier($identifier);

        return $this;
    }

    /**
     * @param $identifier
     *
     * @return $this
     */
    public function load($identifier)
    {
        $this->setIdentifier($identifier);

        $basket = $this->session->get($this->getIdentifier());

        if (empty($basket)) {
            throw (new BasketNotFoundException())->setIdentifier($this->getIdentifier());
        }

        $this->setBasket($basket['basket']);
        $this->setOrder($basket['order']);
        $this->setJurisdiction($basket['jurisdiction']);

        return $this;
    }

    /**
     * @return mixed
     */
    public function products()
    {
        return $this->basket->products();
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return $this->basket->count();
    }

    /**
     * @param $sku
     *
     * @return mixed
     */
    public function pick($sku)
    {
        return $this->basket->pick($sku);
    }

    /**
     * @param Product $product
     */
    public function add(Product $product)
    {
        $this->basket->add($product);

        $this->reconcile();
    }

    /**
     * @param $sku
     * @param Closure $action
     */
    public function update($sku, Closure $action)
    {
        $this->basket->update($sku, $action);

        $this->reconcile();
    }

    /**
     * @param $sku
     */
    public function remove($sku)
    {
        $this->basket->remove($sku);

        $this->reconcile();
    }

    /**
     * @param Discount $discount
     */
    public function discount(Discount $discount)
    {
        $this->basket->discount($discount);

        $this->reconcile();
    }
    
    /**
     * @param Money $delivery
     */
    public function delivery(Money $delivery)
    {
        $this->basket->delivery($delivery);

        $this->reconcile();
    }

    /**
     * @param $sku
     *
     * @return mixed
     */
    public function has($sku)
    {
        return $this->basket->products()->containsKey($sku);
    }

    /**
     * @param $sku
     */
    public function increment($sku)
    {
        return $this->update($sku, function ($product) {
            $product->increment();
        });
    }

    /**
     * @param $sku
     */
    public function decrement($sku)
    {
        return $this->update($sku, function ($product) {
            $product->decrement();
        });
    }

    /**
     * @param $sku
     * @param $quantity
     */
    public function quantity($sku, $quantity)
    {
        return $this->update($sku, function ($product) use ($quantity) {
            $product->quantity($quantity);
        });
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->basket->rate();
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->basket->currency();
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
     * @return mixed
     */
    public function reconcile()
    {
        $reconciler = new DefaultReconciler();

        $meta = [
            new DeliveryMetaData($reconciler),
            new DiscountMetaData($reconciler),
            new ProductsMetaData(),
            new SubtotalMetaData($reconciler),
            new TaxableMetaData(),
            new TaxMetaData($reconciler),
            new TotalMetaData($reconciler),
            new ValueMetaData($reconciler),
        ];

        $processor = new Processor($reconciler, $meta);
        $transformer = new ArrayTransformer(new Converter());

        $order = $processor->process($this->basket);

        $this->setOrder($transformer->transform($order));

        $this->saveBasket();

        return $this->getOrder();
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
     * @return string
     */
    private function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return mixed
     */
    private function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $this->prefix.$identifier;
    }

    /**
     * @return mixed
     */
    private function getSession()
    {
        return $this->session;
    }

    /**
     * @param SessionManager $session
     */
    private function setSession(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * @return mixed
     */
    private function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * @param Jurisdiction $jurisdiction
     */
    private function setJurisdiction(Jurisdiction $jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
    }

    /**
     * @return mixed
     */
    private function getBasket()
    {
        return $this->basket;
    }

    /**
     * @param BaseBasket $basket
     */
    private function setBasket(BaseBasket $basket)
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
    private function setOrder(array $order)
    {
        $this->order = $order;
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

    private function saveBasket()
    {
        $this->session->put($this->getIdentifier(), [
            'basket'       => $this->getBasket(),
            'order'        => $this->getOrder(),
            'jurisdiction' => $this->getJurisdiction(),
        ]);

        $this->session->save();
    }
}
