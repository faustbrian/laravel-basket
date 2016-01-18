<?php

namespace DraperStudio\LaravelBasket;

use Closure;
use DraperStudio\Basket\Basket as BaseBasket;
use DraperStudio\Basket\Collection;
use DraperStudio\Basket\Contracts\Discount;
use DraperStudio\Basket\Contracts\Jurisdiction;
use DraperStudio\Basket\Converter;
use DraperStudio\Basket\MetaData\DeliveryMetaData;
use DraperStudio\Basket\MetaData\DiscountMetaData;
use DraperStudio\Basket\MetaData\ProductsMetaData;
use DraperStudio\Basket\MetaData\SubtotalMetaData;
use DraperStudio\Basket\MetaData\TaxableMetaData;
use DraperStudio\Basket\MetaData\TaxMetaData;
use DraperStudio\Basket\MetaData\TotalMetaData;
use DraperStudio\Basket\MetaData\ValueMetaData;
use DraperStudio\Basket\Processor;
use DraperStudio\Basket\Product;
use DraperStudio\Basket\Reconcilers\DefaultReconciler;
use DraperStudio\Basket\Transformers\ArrayTransformer;
use DraperStudio\LaravelBasket\Exceptions\BasketNotFoundException;
use Illuminate\Session\SessionManager;
use Money\Currency;
use Money\Money;

class Basket
{
    private $prefix = 'basket_';

    private $session;

    private $basket;

    private $identifier;

    private $order;

    public function __construct(SessionManager $session)
    {
        $this->setSession($session);
    }

    public function setup($identifier, Jurisdiction $jurisdiction)
    {
        $this->setBasket(new BaseBasket($jurisdiction));
        $this->setJurisdiction($jurisdiction);
        $this->setIdentifier($identifier);

        return $this;
    }

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

    public function products()
    {
        return $this->basket->products();
    }

    public function count()
    {
        return $this->basket->count();
    }

    public function pick($sku)
    {
        return $this->basket->pick($sku);
    }

    public function add(Product $product)
    {
        $this->basket->add($product);

        $this->reconcile();
    }

    public function update($sku, Closure $action)
    {
        $this->basket->update($sku, $action);

        $this->reconcile();
    }

    public function remove($sku)
    {
        $this->basket->remove($sku);

        $this->reconcile();
    }

    public function discount(Discount $discount)
    {
        $this->basket->discount($discount);

        $this->reconcile();
    }

    public function has($sku)
    {
        return $this->basket->products()->containsKey($sku);
    }

    public function increment($sku)
    {
        return $this->update($sku, function ($product) {
            $product->increment();
        });
    }

    public function decrement($sku)
    {
        return $this->update($sku, function ($product) {
            $product->decrement();
        });
    }

    public function quantity($sku, $quantity)
    {
        return $this->update($sku, function ($product) use ($quantity) {
            $product->quantity($quantity);
        });
    }

    public function getRate()
    {
        return $this->basket->rate();
    }

    public function getCurrency()
    {
        return $this->basket->currency();
    }

    public function getDelivery()
    {
        return $this->order['delivery'];
    }

    public function getDiscount()
    {
        return $this->order['discount'];
    }

    public function getProductsCount()
    {
        return $this->order['products_count'];
    }

    public function getSubtotal()
    {
        return $this->order['subtotal'];
    }

    public function getTaxable()
    {
        return $this->order['taxable'];
    }

    public function getTax()
    {
        return $this->order['tax'];
    }

    public function getTotal()
    {
        return $this->order['total'];
    }

    public function getValue()
    {
        return $this->order['value'];
    }

    public function getProducts()
    {
        return $this->order['products'];
    }

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

    public function addQuick($sku, $name, $price, $actions = [])
    {
        $price = new Money($price, $this->jurisdiction->currency());

        $product = new Product($sku, $name, $price, $this->basket->rate());
        $product->action($this->productClosure($actions));

        $this->basket->add($product);

        $this->reconcile();
    }

    public function updateQuick($sku, $actions = [])
    {
        $this->basket->update($sku, $this->productClosure($actions));

        $this->reconcile();
    }

    private function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    private function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $this->prefix.$identifier;
    }

    private function getSession()
    {
        return $this->session;
    }

    private function setSession(SessionManager $session)
    {
        $this->session = $session;
    }

    private function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    private function setJurisdiction(Jurisdiction $jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
    }

    private function getBasket()
    {
        return $this->basket;
    }

    private function setBasket(BaseBasket $basket)
    {
        $this->basket = $basket;
    }

    private function getOrder()
    {
        return $this->order;
    }

    private function setOrder(array $order)
    {
        $this->order = $order;
    }

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
            'basket' => $this->getBasket(),
            'order' => $this->getOrder(),
            'jurisdiction' => $this->getJurisdiction(),
        ]);

        $this->session->save();
    }
}
