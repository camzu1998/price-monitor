<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceAlert>
 */
class PriceAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $condition = fake()->randomElement(['below', 'above', 'equals', 'percent_drop']);

        return [
            'product_id' => Product::factory(),
            'email' => fake()->safeEmail(),
            'target_price' => fake()->randomFloat(2, 20, 1500),
            'condition' => $condition,
            'percent_threshold' => $condition === 'percent_drop' ? fake()->randomFloat(1, 5, 30) : null,
            'notification_channel' => fake()->randomElement(['email', 'slack', 'webhook']),
            'notification_config' => [
                'template' => 'price_alert',
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
                'include_product_image' => true,
            ],
            'is_active' => fake()->boolean(90),
            'last_triggered_at' => fake()->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'trigger_count' => fake()->numberBetween(0, 10),
        ];
    }

    public function below(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'below',
            'target_price' => $price,
            'percent_threshold' => null,
        ]);
    }

    public function percentDrop(float $percent): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'percent_drop',
            'percent_threshold' => $percent,
        ]);
    }

    public function triggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_triggered_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'trigger_count' => fake()->numberBetween(1, 5),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
