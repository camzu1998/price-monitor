<?php

namespace App\DTOs;

use Carbon\Carbon;

class PriceHistoryFilterDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
        public readonly ?string $source = null,
        public readonly int $limit = 100,
    ) {}

    public static function fromRequest(int $productId, array $data): self
    {
        return new self(
            productId: $productId,
            from: isset($data['from']) ? Carbon::parse($data['from']) : null,
            to: isset($data['to']) ? Carbon::parse($data['to']) : null,
            source: $data['source'] ?? null,
            limit: $data['limit'] ?? 100,
        );
    }

    public function hasDateRange(): bool
    {
        return $this->from !== null || $this->to !== null;
    }

    public function hasFromDate(): bool
    {
        return $this->from !== null;
    }

    public function hasToDate(): bool
    {
        return $this->to !== null;
    }

    public function hasSourceFilter(): bool
    {
        return !empty($this->source);
    }
}
