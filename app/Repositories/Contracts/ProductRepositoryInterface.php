<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findByIdWithIncludes(int $id, array $includes = []): ?Product;

    public function search(array $filters, array $includes = []): LengthAwarePaginator;

    public function getByCategory(string $category, int $limit = 10): Collection;

    public function searchByName(string $searchTerm, int $limit = 10): Collection;
}
