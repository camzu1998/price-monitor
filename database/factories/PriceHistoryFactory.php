<?php

namespace Database\Factories;

use App\Models\ProductSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceHistory>
 */
class PriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 2000);
        $previousPrice = fake()->optional(0.8)->randomFloat(2, $price * 0.7, $price * 1.3);

        return [
            'product_source_id' => ProductSource::factory(),
            'price' => $price,
            'previous_price' => $previousPrice,
            'currency' => 'PLN',
            'is_available' => fake()->boolean(95), // 95% available
            'raw_data' => [
                'html_snippet' => '<span class="price">' . $price . '</span>',
                'scraped_text' => $price . ' PLN',
                'response_time' => fake()->randomFloat(2, 0.5, 3.0),
            ],
            'metadata' => [
                'scraper_version' => '1.0.0',
                'user_agent' => 'PriceBot/1.0',
                'ip_address' => fake()->ipv4(),
                'response_code' => fake()->randomElement([200, 200, 200, 429, 503]), // Mostly 200
            ],
            'scraped_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
            'raw_data' => [
                'html_snippet' => '<span class="unavailable">Out of stock</span>',
                'scraped_text' => 'Temporarily unavailable',
            ],
        ]);
    }

    public function priceDropped(): static
    {
        return $this->state(function (array $attributes) {
            $currentPrice = fake()->randomFloat(2, 50, 500);
            $previousPrice = $currentPrice * fake()->randomFloat(2, 1.1, 1.5); // 10-50% higher

            return [
                'price' => $currentPrice,
                'previous_price' => $previousPrice,
            ];
        });
    }

    public function priceIncreased(): static
    {
        return $this->state(function (array $attributes) {
            $previousPrice = fake()->randomFloat(2, 50, 500);
            $currentPrice = $previousPrice * fake()->randomFloat(2, 1.1, 1.3); // 10-30% higher

            return [
                'price' => $currentPrice,
                'previous_price' => $previousPrice,
            ];
        });
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'scraped_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    public function withCurrency(string $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => $currency,
        ]);
    }
}
