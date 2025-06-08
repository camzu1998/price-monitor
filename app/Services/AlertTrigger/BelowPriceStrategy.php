<?php

namespace App\Services\AlertTrigger;

use App\Enums\AlertCondition;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use App\Services\Contracts\AlertTriggerStrategyInterface;

class BelowPriceStrategy implements AlertTriggerStrategyInterface
{
    public function shouldTrigger(PriceAlert $alert, PriceHistory $currentPrice): bool
    {
        if (!$alert->is_active || !$currentPrice->is_available || !$alert->target_price) {
            return false;
        }

        return $currentPrice->price < $alert->target_price;
    }

    public function getConditionName(): string
    {
        return AlertCondition::BELOW->value;
    }

    public function getDescription(PriceAlert $alert): string
    {
        return "Price falls below {$alert->getFormattedTargetPriceAttribute()}";
    }
}
