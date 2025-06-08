<?php

namespace App\Services\AlertTrigger;

use App\Enums\AlertCondition;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use App\Services\Contracts\AlertTriggerStrategyInterface;

class PercentIncreaseStrategy implements AlertTriggerStrategyInterface
{
    public function shouldTrigger(PriceAlert $alert, PriceHistory $currentPrice): bool
    {
        if (!$alert->is_active || !$currentPrice->is_available || !$alert->percent_threshold) {
            return false;
        }

        if (!$currentPrice->previous_price || $currentPrice->previous_price <= 0) {
            return false;
        }

        $priceChangePercent = $currentPrice->getPriceChangePercentAttribute();

        if ($priceChangePercent === null) {
            return false;
        }

        return $priceChangePercent >= $alert->percent_threshold;
    }

    public function getConditionName(): string
    {
        return AlertCondition::PERCENT_INCREASE->value;
    }

    public function getDescription(PriceAlert $alert): string
    {
        return "Price increases by {$alert->percent_threshold}% or more";
    }
}
