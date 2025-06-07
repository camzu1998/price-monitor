<?php

namespace Database\Seeders;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::active()->take(15)->get();

        foreach ($products as $product) {
            $alertsCount = rand(0, 3);

            for ($i = 0; $i < $alertsCount; $i++) {
                PriceAlert::factory()
                    ->for($product)
                    ->create();
            }
        }

        $this->createTestAlerts();

        $this->command->info('Created ' . PriceAlert::count() . ' price alerts');
    }

    private function createTestAlerts(): void
    {
        $iphone = Product::where('sku', 'APL-IPH15P')->first();

        if ($iphone) {
            PriceAlert::factory()
                ->for($iphone)
                ->below(4000)
                ->state(['email' => 'demo@example.com'])
                ->create();

            PriceAlert::factory()
                ->for($iphone)
                ->percentDrop(10)
                ->state(['email' => 'bargain@example.com'])
                ->create();
        }

        PriceAlert::factory()
            ->triggered()
            ->count(3)
            ->create();

        PriceAlert::factory()
            ->inactive()
            ->count(2)
            ->create();
    }
}
