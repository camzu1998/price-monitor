<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Product;
use App\Models\ProductSource;
use App\Models\PriceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PriceHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
    }

    #[Test]
    public function it_can_get_product_price_history()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        PriceHistory::factory()->for($source)->count(5)->create();

        $response = $this->getJson("/api/products/{$product->id}/price-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'price',
                        'previous_price',
                        'currency',
                        'is_available',
                        'price_change',
                        'price_change_percent',
                        'source_name',
                        'scraped_at'
                    ]
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_price_history_by_date_range()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        PriceHistory::factory()->for($source)->count(3)->create([
            'scraped_at' => Carbon::now()->subDays(10)
        ]);

        PriceHistory::factory()->for($source)->count(2)->create([
            'scraped_at' => Carbon::now()->subDays(2)
        ]);

        $response = $this->getJson("/api/products/{$product->id}/price-history?from=" . Carbon::now()->subDays(5)->toDateString());

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_price_history_by_source()
    {
        $product = Product::factory()->create();
        $amazonSource = ProductSource::factory()->for($product)->amazon()->create();
        $allegroSource = ProductSource::factory()->for($product)->allegro()->create();

        PriceHistory::factory()->for($amazonSource)->count(3)->create();
        PriceHistory::factory()->for($allegroSource)->count(2)->create();

        $response = $this->getJson("/api/products/{$product->id}/price-history?source=amazon");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_returns_price_history_ordered_by_date_desc()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        $oldPrice = PriceHistory::factory()->for($source)->create([
            'scraped_at' => Carbon::now()->subDays(5),
            'price' => 100
        ]);

        $newPrice = PriceHistory::factory()->for($source)->create([
            'scraped_at' => Carbon::now()->subDays(1),
            'price' => 90
        ]);

        $response = $this->getJson("/api/products/{$product->id}/price-history");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(90, $data[0]['price']);
        $this->assertEquals(100, $data[1]['price']);
    }
}
