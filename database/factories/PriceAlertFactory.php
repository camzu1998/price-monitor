<?php

namespace Database\Factories;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Enums\AlertCondition;
use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceAlertFactory extends Factory
{
    protected $model = PriceAlert::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'email' => $this->faker->email(),
            'condition' => AlertCondition::BELOW->value, // DEFAULT condition for predictability
            'target_price' => 1000.00, // DEFAULT target price
            'percent_threshold' => null,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true,
            'trigger_count' => 0,
            'last_triggered_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Below price condition
     */
    public function belowPrice(float $targetPrice = 1000.00): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => AlertCondition::BELOW->value,
            'target_price' => $targetPrice,
            'percent_threshold' => null,
        ]);
    }

    /**
     * Above price condition
     */
    public function abovePrice(float $targetPrice = 1000.00): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => AlertCondition::ABOVE->value,
            'target_price' => $targetPrice,
            'percent_threshold' => null,
        ]);
    }

    /**
     * Percent drop condition
     */
    public function percentDrop(float $threshold = 15.0): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => AlertCondition::PERCENT_DROP->value,
            'target_price' => 0,
            'percent_threshold' => $threshold,
        ]);
    }

    /**
     * Percent increase condition
     */
    public function percentIncrease(float $threshold = 20.0): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => AlertCondition::PERCENT_INCREASE->value,
            'target_price' => 0,
            'percent_threshold' => $threshold,
        ]);
    }

    /**
     * Alert for specific user
     */
    public function forUser(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Active alert
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Inactive alert
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Alert with specific trigger count
     */
    public function withTriggers(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_count' => $count,
            'last_triggered_at' => $count > 0 ? now()->subDays(rand(1, 30)) : null,
        ]);
    }

    /**
     * SMS notification channel
     */
    public function smsNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_channel' => NotificationChannel::SMS->value,
        ]);
    }
}
