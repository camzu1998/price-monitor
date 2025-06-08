<?php

namespace Database\Factories;

use App\Models\PriceHistory;
use App\Models\ProductSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceHistoryFactory extends Factory
{
    protected $model = PriceHistory::class;

    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 100, 2000);

        return [
            'product_source_id' => ProductSource::factory(),
            'price' => $price,
            'previous_price' => $price * 0.9, // DEFAULT: 10% lower than current (for predictable tests)
            'currency' => 'PLN',
            'is_available' => true,
            'scraped_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Price with NO previous price (first scrape)
     */
    public function firstPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'previous_price' => null,
        ]);
    }

    /**
     * Price with INCREASE from previous
     */
    public function priceIncrease(float $percent = 25.0): static
    {
        return $this->state(function (array $attributes) use ($percent) {
            $currentPrice = $attributes['price'];
            $previousPrice = $currentPrice / (1 + ($percent / 100));

            return [
                'previous_price' => round($previousPrice, 2),
            ];
        });
    }

    /**
     * Price with DROP from previous
     */
    public function priceDrop(float $percent = 20.0): static
    {
        return $this->state(function (array $attributes) use ($percent) {
            $currentPrice = $attributes['price'];
            $previousPrice = $currentPrice / (1 - ($percent / 100));

            return [
                'previous_price' => round($previousPrice, 2),
            ];
        });
    }

    /**
     * Specific price with exact previous price
     */
    public function withPrices(float $current, float $previous = null): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $current,
            'previous_price' => $previous,
        ]);
    }

    /**
     * Available product
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
        ]);
    }

    /**
     * Unavailable product
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
