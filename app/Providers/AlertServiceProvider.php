<?php

namespace App\Providers;

use App\Services\AlertService;
use App\Services\ProductService;
use App\Services\PriceHistoryService;
use App\Services\AlertTrigger\AlertTriggerStrategyFactory;
use App\Repositories\AlertRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PriceHistoryRepository;
use App\Repositories\Contracts\AlertRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PriceHistoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AlertServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(AlertRepositoryInterface::class, AlertRepository::class);
        $this->app->bind(PriceHistoryRepositoryInterface::class, PriceHistoryRepository::class);

        // Register Strategy Factory as singleton
        $this->app->singleton(AlertTriggerStrategyFactory::class, function () {
            return new AlertTriggerStrategyFactory();
        });

        // Register Alert Service
        $this->app->bind(AlertService::class, function ($app) {
            return new AlertService(
                $app->make(AlertRepositoryInterface::class),
                $app->make(AlertTriggerStrategyFactory::class)
            );
        });

        // Register Price History Service
        $this->app->bind(PriceHistoryService::class, function ($app) {
            return new PriceHistoryService(
                $app->make(PriceHistoryRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
