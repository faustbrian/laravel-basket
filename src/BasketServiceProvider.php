<?php

namespace BrianFaust\Laravel\Basket;

use BrianFaust\ServiceProvider\AbstractServiceProvider as ServiceProvider;

class BasketServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->publishConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfig();

        $this->registerSession();

        $this->registerManager();
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [
            'basket',
            'basket.session',
        ];
    }

    /**
     * Register the session driver used by the Basket.
     */
    protected function registerSession(): void
    {
        $this->app->bind('basket.session', function ($app) {
            $config = $app['config']->get('basket');

            return new Storage\IlluminateSession($app['session.store'], $config['instance'], $config['session_key']);
        });
    }

    /**
     * Register the Basket.
     */
    protected function registerManager(): void
    {
        $this->app->bind('basket', function ($app) {
            return new BasketManager($app['basket.session'], $app['events'], new $app['config']['basket']['jurisdiction']());
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageName(): string
    {
        return 'basket';
    }
}
