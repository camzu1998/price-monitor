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

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // Register Product Service
        $this->app->bind(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepositoryInterface::class)
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
