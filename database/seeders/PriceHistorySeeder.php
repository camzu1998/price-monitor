<?php

namespace Database\Seeders;

use App\Models\PriceHistory;
use App\Models\ProductSource;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = ProductSource::all();

        foreach ($sources as $source) {
            $this->generatePriceHistoryForSource($source);
        }

        $this->createPriceDropScenarios();
        $this->createPriceIncreaseScenarios();

        $this->command->info('Created ' . PriceHistory::count() . ' price history records');
    }

    private function generatePriceHistoryForSource(ProductSource $source): void
    {
        $basePrice = rand(50, 1500);
        $currentPrice = $basePrice;
        $startDate = Carbon::now()->subDays(30);

        for ($day = 0; $day <= 30; $day++) {
            $scrapedAt = $startDate->copy()->addDays($day)->addHours(rand(9, 18));

            $fluctuation = rand(-10, 10) / 100;
            $previousPrice = $currentPrice;
            $currentPrice = max(10, $basePrice * (1 + $fluctuation * $day * 0.02));

            PriceHistory::create([
                'product_source_id' => $source->id,
                'price' => round($currentPrice, 2),
                'previous_price' => $day > 0 ? round($previousPrice, 2) : null,
                'currency' => 'PLN',
                'is_available' => rand(1, 100) <= 95, // 95% availability
                'raw_data' => [
                    'html_snippet' => '<span class="price">' . $currentPrice . '</span>',
                    'scraped_text' => $currentPrice . ' PLN',
                    'response_time' => rand(500, 3000) / 1000,
                ],
                'metadata' => [
                    'scraper_version' => '1.0.0',
                    'response_code' => 200,
                ],
                'scraped_at' => $scrapedAt,
                'created_at' => $scrapedAt,
                'updated_at' => $scrapedAt,
            ]);
        }
    }

    private function createPriceDropScenarios(): void
    {
        PriceHistory::factory()
            ->priceDropped()
            ->recent()
            ->count(5)
            ->create();
    }

    private function createPriceIncreaseScenarios(): void
    {
        PriceHistory::factory()
            ->priceIncreased()
            ->recent()
            ->count(3)
            ->create();
    }
}
