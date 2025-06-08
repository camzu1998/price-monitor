<?php

namespace App\Services\AlertTrigger;

use App\Services\Contracts\AlertTriggerStrategyInterface;
use InvalidArgumentException;

class AlertTriggerStrategyFactory
{
    private array $strategies = [];

    public function __construct()
    {
        $this->registerStrategies();
    }

    public function create(string $condition): AlertTriggerStrategyInterface
    {
        if (!isset($this->strategies[$condition])) {
            throw new InvalidArgumentException("Unknown alert condition: {$condition}");
        }

        $strategyClass = $this->strategies[$condition];

        return new $strategyClass();
    }

    public function getAvailableConditions(): array
    {
        return array_keys($this->strategies);
    }

    public function isValidCondition(string $condition): bool
    {
        return isset($this->strategies[$condition]);
    }

    private function registerStrategies(): void
    {
        $this->strategies = [
            'below' => BelowPriceStrategy::class,
            'above' => AbovePriceStrategy::class,
            'percent_drop' => PercentDropStrategy::class,
            'percent_increase' => PercentIncreaseStrategy::class,
        ];
    }
}
