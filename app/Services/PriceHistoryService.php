<?php

namespace App\Services;

use App\DTOs\PriceHistoryFilterDTO;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Repositories\Contracts\PriceHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PriceHistoryService
{
    public function __construct(
        private readonly PriceHistoryRepositoryInterface $priceHistoryRepository
    ) {}

    public function getProductPriceHistory(PriceHistoryFilterDTO $filterDTO): Collection
    {
        $filters = [
            'from' => $filterDTO->from?->toDateString(),
            'to' => $filterDTO->to?->toDateString(),
            'source' => $filterDTO->source,
            'limit' => $filterDTO->limit,
        ];

        return $this->priceHistoryRepository->getProductPriceHistory($filterDTO->productId, $filters);
    }

    public function getLatestPriceForProduct(int $productId): ?PriceHistory
    {
        return $this->priceHistoryRepository->getLatestPriceForProduct($productId);
    }

    public function getPriceStatisticsForProduct(int $productId, int $days = 30): array
    {
        return $this->priceHistoryRepository->getPriceStatisticsForProduct($productId, $days);
    }

    public function getPriceDropsForProduct(int $productId, float $minDropPercent = 5, int $days = 7): Collection
    {
        return $this->priceHistoryRepository->getPriceDropsForProduct($productId, $minDropPercent, $days);
    }

    public function compareSourcePrices(int $productId): Collection
    {
        return $this->priceHistoryRepository->compareSourcePrices($productId);
    }
}
