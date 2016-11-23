# Laravel Basket


Check https://github.com/BrianFaust/Basket to see how the underlying Basket works.
## Installation

Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

``` bash
$ composer require faustbrian/laravel-basket
```

## Usage

#### Default Way of adding a Product

This function should be used when you want to built your "Product"-Objects on your own.

``` php
use BrianFaust\LaravelBasket\Basket;

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
use BrianFaust\LaravelBasket\Basket;

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

## Security

If you discover a security vulnerability within this package, please send an e-mail to Brian Faust at hello@brianfaust.de. All security vulnerabilities will be promptly addressed.

## License

[MIT](LICENSE) © [Brian Faust](https://brianfaust.de)
