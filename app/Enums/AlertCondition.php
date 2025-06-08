<?php

namespace App\Enums;

enum AlertCondition: string
{
    case BELOW = 'below';
    case ABOVE = 'above';
    case EQUALS = 'equals';
    case PERCENT_DROP = 'percent_drop';
    case PERCENT_INCREASE = 'percent_increase';

    public function getLabel(): string
    {
        return match($this) {
            self::BELOW => 'Price falls below',
            self::ABOVE => 'Price rises above',
            self::EQUALS => 'Price equals',
            self::PERCENT_DROP => 'Price drops by percentage',
            self::PERCENT_INCREASE => 'Price increases by percentage',
        };
    }

    public function getDescription(float $targetPrice = null, float $percentThreshold = null): string
    {
        return match($this) {
            self::BELOW => $targetPrice ? "falls below " . number_format($targetPrice, 2) . " PLN" : "falls below target price",
            self::ABOVE => $targetPrice ? "rises above " . number_format($targetPrice, 2) . " PLN" : "rises above target price",
            self::EQUALS => $targetPrice ? "equals " . number_format($targetPrice, 2) . " PLN" : "equals target price",
            self::PERCENT_DROP => $percentThreshold ? "drops by {$percentThreshold}% or more" : "drops by specified percentage",
            self::PERCENT_INCREASE => $percentThreshold ? "increases by {$percentThreshold}% or more" : "increases by specified percentage",
        };
    }

    public function isPriceBasedCondition(): bool
    {
        return in_array($this, [self::BELOW, self::ABOVE, self::EQUALS]);
    }

    public function isPercentBasedCondition(): bool
    {
        return in_array($this, [self::PERCENT_DROP, self::PERCENT_INCREASE]);
    }

    public function requiresTargetPrice(): bool
    {
        return $this->isPriceBasedCondition();
    }

    public function requiresPercentThreshold(): bool
    {
        return $this->isPercentBasedCondition();
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        return array_map(fn($case) => $case->getLabel(), self::cases());
    }
}
