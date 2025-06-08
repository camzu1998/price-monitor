<?php

namespace App\DTOs;

use App\Enums\AlertCondition;
use App\Enums\NotificationChannel;

class AlertDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly string $email,
        public readonly AlertCondition $condition,
        public readonly ?float $targetPrice = null,
        public readonly ?float $percentThreshold = null,
        public readonly NotificationChannel $notificationChannel = NotificationChannel::EMAIL,
        public readonly bool $isActive = true,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            email: $data['email'],
            condition: AlertCondition::from($data['condition']),
            targetPrice: $data['target_price'] ?? null,
            percentThreshold: $data['percent_threshold'] ?? null,
            notificationChannel: NotificationChannel::from($data['notification_channel'] ?? 'email'),
            isActive: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'email' => $this->email,
            'condition' => $this->condition->value,
            'target_price' => $this->targetPrice,
            'percent_threshold' => $this->percentThreshold,
            'notification_channel' => $this->notificationChannel->value,
            'is_active' => $this->isActive,
        ];
    }

    public function isPriceBasedCondition(): bool
    {
        return $this->condition->isPriceBasedCondition();
    }

    public function isPercentBasedCondition(): bool
    {
        return $this->condition->isPercentBasedCondition();
    }
}
