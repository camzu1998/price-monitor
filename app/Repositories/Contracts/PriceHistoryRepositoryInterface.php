<?php

namespace App\Repositories\Contracts;

use App\Models\PriceHistory;
use Illuminate\Database\Eloquent\Collection;

interface PriceHistoryRepositoryInterface
{
    public function getProductPriceHistory(int $productId, array $filters = []): Collection;

    public function getLatestPriceForProduct(int $productId): ?PriceHistory;

    public function getPriceStatisticsForProduct(int $productId, int $days = 30): array;

    public function getPriceDropsForProduct(int $productId, float $minDropPercent = 5, int $days = 7): Collection;

    public function compareSourcePrices(int $productId): Collection;
}
