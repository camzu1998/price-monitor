<?php

namespace App\DTOs;

class ProductSearchDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $category = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
        public readonly ?string $include = null,
        public readonly string $sortBy = 'created_at',
        public readonly string $sortDirection = 'desc',
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            category: $data['category'] ?? null,
            perPage: $data['per_page'] ?? 15,
            page: $data['page'] ?? 1,
            include: $data['include'] ?? null,
            sortBy: $data['sort_by'] ?? 'created_at',
            sortDirection: $data['sort_direction'] ?? 'desc',
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function hasSearch(): bool
    {
        return !empty($this->search);
    }

    public function hasCategory(): bool
    {
        return !empty($this->category);
    }

    public function shouldInclude(string $relation): bool
    {
        return $this->include && str_contains($this->include, $relation);
    }

    public function hasActiveFilter(): bool
    {
        return $this->isActive !== null;
    }
}
