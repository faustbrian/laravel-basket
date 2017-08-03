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

use BrianFaust\ServiceProvider\AbstractServiceProvider as ServiceProvider;

class BasketServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-basket.php' => config_path('laravel-basket.php'),
        ], 'config');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-basket.php', 'laravel-basket');

        $this->registerSession();

        $this->registerManager();
    }

    /**
     * Register the session driver used by the Basket.
     */
    protected function registerSession()
    {
        $this->app->bind('basket.session', function ($app) {
            $config = $app['config']->get('basket');

            return new Storage\IlluminateSession($app['session.store'], $config['instance'], $config['session_key']);
        });
    }

    /**
     * Register the Basket.
     */
    protected function registerManager()
    {
        $this->app->bind('basket', function ($app) {
            return new BasketManager($app['basket.session'], $app['events'], new $app['config']['basket']['jurisdiction']());
        });
    }
}
