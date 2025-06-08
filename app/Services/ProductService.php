<?php

namespace App\Services;

use App\DTOs\ProductSearchDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function searchProducts(ProductSearchDTO $searchDTO): LengthAwarePaginator
    {
        $filters = [
            'search' => $searchDTO->search,
            'category' => $searchDTO->category,
            'per_page' => $searchDTO->perPage,
            'page' => $searchDTO->page,
            'sort_by' => $searchDTO->sortBy,
            'sort_direction' => $searchDTO->sortDirection,
            'is_active' => $searchDTO->isActive,
        ];

        $includes = [];
        if ($searchDTO->shouldInclude('current_price')) {
            $includes['sources.priceHistories'] = function ($query) {
                $query->latest('scraped_at')->take(1);
            };
        }
        if ($searchDTO->shouldInclude('alerts')) {
            $includes[] = 'activeAlerts';
        }

        return $this->productRepository->search($filters, $includes);
    }

    public function findProductWithIncludes(int $productId, ?string $include = null): ?Product
    {
        $includes = [];

        if ($include && str_contains($include, 'current_price')) {
            $includes['sources.priceHistories'] = function ($query) {
                $query->latest('scraped_at')->take(1);
            };
        }

        return $this->productRepository->findByIdWithIncludes($productId, $includes);
    }

    public function getProductsByCategory(string $category, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->productRepository->getByCategory($category, $limit);
    }

    public function searchProductsByName(string $searchTerm, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->productRepository->searchByName($searchTerm, $limit);
    }
}
