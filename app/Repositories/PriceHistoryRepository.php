<?php

namespace App\Repositories;

use App\Models\PriceHistory;
use App\Repositories\Contracts\PriceHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PriceHistoryRepository implements PriceHistoryRepositoryInterface
{
    public function getProductPriceHistory(int $productId, array $filters = []): Collection
    {
        $query = PriceHistory::query()
            ->whereHas('productSource', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->with(['productSource'])
            ->orderBy('scraped_at', 'desc');

        $this->applyFilters($query, $filters);

        $limit = $filters['limit'] ?? 100;
        return $query->limit($limit)->get();
    }

    public function getLatestPriceForProduct(int $productId): ?PriceHistory
    {
        return PriceHistory::whereHas('productSource', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
            ->with(['productSource'])
            ->latest('scraped_at')
            ->first();
    }

    public function getPriceStatisticsForProduct(int $productId, int $days = 30): array
    {
        $prices = PriceHistory::whereHas('productSource', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
            ->where('scraped_at', '>=', now()->subDays($days))
            ->where('is_available', true)
            ->pluck('price');

        if ($prices->isEmpty()) {
            return [
                'count' => 0,
                'min_price' => null,
                'max_price' => null,
                'avg_price' => null,
                'price_volatility' => null
            ];
        }

        $minPrice = $prices->min();
        $maxPrice = $prices->max();
        $avgPrice = round($prices->avg(), 2);
        $volatility = $maxPrice > 0 ? round((($maxPrice - $minPrice) / $maxPrice) * 100, 2) : 0;

        return [
            'count' => $prices->count(),
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'avg_price' => $avgPrice,
            'price_volatility' => $volatility
        ];
    }

    public function getPriceDropsForProduct(int $productId, float $minDropPercent = 5, int $days = 7): Collection
    {
        return PriceHistory::whereHas('productSource', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
            ->with(['productSource'])
            ->where('scraped_at', '>=', now()->subDays($days))
            ->priceDrops($minDropPercent)
            ->orderBy('scraped_at', 'desc')
            ->get();
    }

    public function compareSourcePrices(int $productId): Collection
    {
        return PriceHistory::whereHas('productSource', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
            ->with(['productSource'])
            ->whereIn('id', function ($query) use ($productId) {
                $query->select('id')
                    ->from('price_histories')
                    ->whereHas('productSource', function ($q) use ($productId) {
                        $q->where('product_id', $productId);
                    })
                    ->orderBy('scraped_at', 'desc')
                    ->groupBy('product_source_id')
                    ->limit(1);
            })
            ->orderBy('price')
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['from'])) {
            $query->whereDate('scraped_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('scraped_at', '<=', $filters['to']);
        }

        if (!empty($filters['source'])) {
            $query->whereHas('productSource', function ($query) use ($filters) {
                $query->where('source_name', 'ILIKE', "%{$filters['source']}%");
            });
        }
    }
}
