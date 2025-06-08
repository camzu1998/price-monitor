<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findByIdWithIncludes(int $id, array $includes = []): ?Product
    {
        $query = Product::where('id', $id);

        if (!empty($includes)) {
            $query->with($includes);
        }

        return $query->first();
    }

    public function search(array $filters, array $includes = []): LengthAwarePaginator
    {
        $query = Product::query();

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        if (!empty($includes)) {
            $query->with($includes);
        }

        return $query->paginate(
            $filters['per_page'] ?? 15,
            ['*'],
            'page',
            $filters['page'] ?? 1
        );
    }

    public function getByCategory(string $category, int $limit = 10): Collection
    {
        return Product::active()
            ->byCategory($category)
            ->limit($limit)
            ->get();
    }

    public function searchByName(string $searchTerm, int $limit = 10): Collection
    {
        return Product::active()
            ->search($searchTerm)
            ->limit($limit)
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['is_active'])) {
            if ($filters['is_active']) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($sortBy === 'price') {
            $query->leftJoin('product_sources', 'products.id', '=', 'product_sources.product_id')
                ->leftJoin('price_histories', function ($join) {
                    $join->on('product_sources.id', '=', 'price_histories.product_source_id')
                        ->whereRaw('price_histories.scraped_at = (
                               SELECT MAX(scraped_at)
                               FROM price_histories ph2
                               WHERE ph2.product_source_id = product_sources.id
                           )');
                })
                ->select('products.*')
                ->orderBy('price_histories.price', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
    }
}
