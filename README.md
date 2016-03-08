# Laravel Basket

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Check https://github.com/draperstudio/Basket to see how the underlying Basket works.

## Install

Via Composer

``` bash
$ composer require draperstudio/laravel-basket
```

## Usage

#### Default Way of adding a Product

This function should be used when you want to built your "Product"-Objects on your own.

``` php
use DraperStudio\LaravelBasket\Basket;

get('/', function (Basket $basket) {
    // Setup the Basket for UK
    $basket = $basket->setup('uuid_for_example', new UnitedKingdom());

    // Build a new Product
    $product = new Product(1, 'Four Steps to the Epiphany', new Money(1000, new Currency('GBP')), new UnitedKingdomValueAddedTax());
    $product->action(function ($product) {
            $product->quantity(1);
            $product->freebie(false);
            $product->taxable(false);
            $product->delivery(new Money(500, new Currency('GBP')));
            $product->coupon($coupon);
            $product->tags(new Collection(['four steps', 'movie', 'cinema']));
            $product->discount(new PercentageDiscount(20));
            $product->category(new PhysicalBook());
    });

    // Add a new Product
    $basket->add($product);
});
```

#### **Lazy/Convenient** Way of adding a Product

This method should be used if you want to have your "Product"-Objects build by
LaravelBasket. It will create the "Product"-Object and build all objects that
are required like "Money"-Objects for delivery costs.

``` php
use DraperStudio\LaravelBasket\Basket;

get('/', function (Basket $basket) {
    // Setup the Basket for UK
    $basket = $basket->setup('uuid_for_example', new UnitedKingdom());

    // Add a new Product
    $basket->addQuick(1, 'Four Steps to the Epiphany', 1000, [
        'quantity' => 1,
        'freebie' => false,
        'taxable' => false,
        'delivery' => 500,
        'coupon' => $coupon,
        'tags' => ['four steps', 'movie', 'cinema'];
        'discount' => new PercentageDiscount(20),
        'category' => new PhysicalBook(),
    ]);
});
```

## Methods

#### Setup a new Basket with the given Jurisdictions
``` php
$basket->boot(Jurisdiction $jurisdiction);
```

#### Set the basket instance to the "identifier"-instance
``` php
$basket->load($identifier);
```

#### Get the products in the Basket
``` php
$basket->products();
```

#### Get the number of products in the Basket
``` php
$basket->count();
```

#### Pick a product from the Basket
``` php
$basket->pick($sku);
```

#### Add a new product to the Basket
``` php
$basket->add(Product $product);
```

#### Update a product in the Basket
``` php
$basket->update($sku, Closure $action)
```

#### Add a new product to the Basket **(Lazy/Convenience Version)**
``` php
$basket->addQuick($sku, $name, $price, $actions = []);
```

#### Update a product in the Basket **(Lazy/Convenience Version)**
``` php
$basket->updateQuick($sku, $actions = []);
```

#### Remove a product from the Basket
``` php
$basket->remove($sku);
```

#### Apply a discount to the Basket
``` php
$basket->discount(Discount $discount);
```

#### Get the Jurisdiction tax rate of the Basket
``` php
$basket->getRate();
```

#### Get the Jurisdiction currency of the Basket
``` php
$basket->getCurrency();
```

#### Get the delivery cost of the Order
``` php
$basket->getDelivery();
```

#### Get the discount of the Order
``` php
$basket->getDiscount();
```

#### Get the number of products of the Order
``` php
$basket->getProductsCount();
```

#### Get the subtotal of the Order
``` php
$basket->getSubtotal();
```

#### Get the taxable status of the Order
``` php
$basket->getTaxable();
```

#### Get the tax of the Order
``` php
$basket->getTax();
```

#### Get the total value of the Order
``` php
$basket->getTotal();
```

#### Get the value of the Order
``` php
$basket->getValue();
```

#### Get the products of the Order
``` php
$basket->getProducts();
```

#### Perform Reconciliation and return the Order
``` php
$basket->reconcile();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hello@draperstudio.tech instead of using the issue tracker.

## Credits

- [DraperStudio][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/DraperStudio/laravel-basket.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/DraperStudio/Laravel-Basket/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/DraperStudio/laravel-basket.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/DraperStudio/laravel-basket.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/DraperStudio/laravel-basket.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/DraperStudio/laravel-basket
[link-travis]: https://travis-ci.org/DraperStudio/Laravel-Basket
[link-scrutinizer]: https://scrutinizer-ci.com/g/DraperStudio/laravel-basket/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/DraperStudio/laravel-basket
[link-downloads]: https://packagist.org/packages/DraperStudio/laravel-basket
[link-author]: https://github.com/DraperStudio
[link-contributors]: ../../contributors
