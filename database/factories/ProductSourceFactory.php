<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductSource>
 */
class ProductSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = [
            'amazon' => [
                'base_url' => 'https://www.amazon.com/dp/',
                'price_selector' => '.a-price-whole',
                'availability_selector' => '#availability span',
            ],
            'ebay' => [
                'base_url' => 'https://www.ebay.com/itm/',
                'price_selector' => '.display-price',
                'availability_selector' => '.vi-acc-del-range',
            ],
            'allegro' => [
                'base_url' => 'https://allegro.pl/oferta/',
                'price_selector' => '[data-box-name="Price"] span',
                'availability_selector' => '[data-analytics-interaction="listing.delivery"]',
            ],
        ];

        $sourceName = fake()->randomElement(array_keys($sources));
        $sourceConfig = $sources[$sourceName];

        return [
            'product_id' => Product::factory(),
            'source_name' => $sourceName,
            'source_url' => $sourceConfig['base_url'] . fake()->regexify('[A-Z0-9]{10}'),
            'price_selector' => $sourceConfig['price_selector'],
            'availability_selector' => $sourceConfig['availability_selector'],
            'scraper_config' => [
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'delay_min' => 1,
                'delay_max' => 3,
                'timeout' => 30,
                'retry_count' => 3,
            ],
            'is_active' => fake()->boolean(85),
            'last_scraped_at' => fake()->optional(0.7)->dateTimeBetween('-1 week'),
        ];
    }

    public function amazon(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_name' => 'amazon',
            'source_url' => 'https://www.amazon.com/dp/' . fake()->regexify('[A-Z0-9]{10}'),
            'price_selector' => '.a-price-whole',
        ]);
    }

    public function allegro(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_name' => 'allegro',
            'source_url' => 'https://allegro.pl/oferta/' . fake()->regexify('[a-z0-9]{8}'),
            'price_selector' => '[data-box-name="Price"] span',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function needsScraping(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'last_scraped_at' => fake()->dateTimeBetween('-2 hours', '-1 hour'),
        ]);
    }
}
