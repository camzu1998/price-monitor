<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Product::all() as $product) {
            $sourcesCount = rand(1, 3);
            $platforms = ['amazon', 'allegro', 'ebay'];
            $selectedPlatforms = collect($platforms)->random($sourcesCount);

            foreach ($selectedPlatforms as $platform) {
                ProductSource::factory()
                    ->for($product)
                    ->state([
                        'source_name' => $platform,
                    ])
                    ->create();
            }
        }

        ProductSource::factory()
            ->needsScraping()
            ->count(5)
            ->create();

        ProductSource::factory()
            ->inactive()
            ->count(3)
            ->create();

        $this->command->info('Created ' . ProductSource::count() . ' product sources');
    }
}
