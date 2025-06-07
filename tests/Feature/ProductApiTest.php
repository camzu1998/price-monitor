<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Product;
use App\Models\ProductSource;
use App\Models\PriceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
    }

    #[Test]
    public function it_can_get_all_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'description',
                        'category',
                        'image_url',
                        'is_active',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page'
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_can_get_single_product()
    {
        $product = Product::factory()->create([
            'name' => 'iPhone 15 Pro',
            'sku' => 'APL-IPH15P'
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'iPhone 15 Pro',
                    'sku' => 'APL-IPH15P'
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_product()
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found'
            ]);
    }

    #[Test]
    public function it_can_search_products()
    {
        Product::factory()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy']);
        Product::factory()->create(['name' => 'iPhone 14']);

        $response = $this->getJson('/api/products?search=iPhone');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_products_by_category()
    {
        Product::factory()->count(2)->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Books']);

        $response = $this->getJson('/api/products?category=Electronics');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_get_product_with_current_price()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();
        $priceHistory = PriceHistory::factory()->for($source)->create([
            'price' => 1299.99,
            'scraped_at' => now()
        ]);

        $response = $this->getJson("/api/products/{$product->id}?include=current_price");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'current_price' => [
                        'price',
                        'currency',
                        'source_name',
                        'scraped_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_paginates_products()
    {
        Product::factory()->count(25)->create();

        $response = $this->getJson('/api/products?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonPath('meta.total', 25);
        $response->assertJsonPath('meta.per_page', 10);
    }
}
