<?php

namespace App\Services\Contracts;

use App\Models\PriceAlert;
use App\Models\PriceHistory;

interface AlertTriggerStrategyInterface
{
    public function shouldTrigger(PriceAlert $alert, PriceHistory $currentPrice): bool;

    public function getConditionName(): string;

    public function getDescription(PriceAlert $alert): string;
}
