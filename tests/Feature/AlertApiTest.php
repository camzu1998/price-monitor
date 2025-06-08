<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Product;
use App\Models\ProductSource;
use App\Models\PriceHistory;
use App\Models\PriceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlertApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    #[Test]
    public function it_can_create_price_alert()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'target_price' => 999.99,
            'condition' => 'below',
            'notification_channel' => 'email'
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'product_id' => $product->id,
                'email' => 'test@example.com',
                'target_price' => "999.99",
                'condition' => 'below',
                'is_active' => true
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'target_price' => 999.99
        ]);
    }

    #[Test]
    public function it_can_create_percent_drop_alert()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => 'percent_drop',
            'target_price' => 0,
            'percent_threshold' => 15.0,
            'notification_channel' => 'email'
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'condition' => 'percent_drop',
                    'percent_threshold' => 15.0
                ]
            ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_alert()
    {
        $response = $this->postJson('/api/alerts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
                'email',
                'condition',
                'notification_channel'
            ]);
    }

    #[Test]
    public function it_validates_target_price_required_for_price_conditions()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => 'below',
            'notification_channel' => 'email'
            // Missing target_price
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_price']);
    }

    #[Test]
    public function it_validates_percent_threshold_required_for_percent_conditions()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => 'percent_drop',
            'notification_channel' => 'email'
            // Missing percent_threshold
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['percent_threshold']);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'invalid-email',
            'target_price' => 999.99,
            'condition' => 'below',
            'notification_channel' => 'email'
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_validates_condition_enum_values()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => 'invalid_condition',
            'target_price' => 999.99,
            'notification_channel' => 'email'
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['condition']);
    }

    #[Test]
    public function it_can_get_alerts_for_user()
    {
        $userEmail = 'user@example.com';
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        PriceAlert::factory()->for($product1)->create(['email' => $userEmail]);
        PriceAlert::factory()->for($product2)->create(['email' => $userEmail]);
        PriceAlert::factory()->create(['email' => 'other@example.com']);

        $response = $this->getJson("/api/alerts?email={$userEmail}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'email',
                        'target_price',
                        'condition',
                        'percent_threshold',
                        'notification_channel',
                        'is_active',
                        'last_triggered_at',
                        'trigger_count',
                        'created_at',
                        'product' => [
                            'id',
                            'name',
                            'sku'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_get_single_alert()
    {
        $alert = PriceAlert::factory()->create([
            'email' => 'test@example.com',
            'target_price' => 1299.99
        ]);

        $response = $this->getJson("/api/alerts/{$alert->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $alert->id,
                    'email' => 'test@example.com',
                    'target_price' => 1299.99
                ]
            ]);
    }

    #[Test]
    public function it_can_update_alert()
    {
        $alert = PriceAlert::factory()->create([
            'target_price' => 1000.00,
            'is_active' => true
        ]);

        $updateData = [
            'target_price' => 899.99,
            'is_active' => false
        ];

        $response = $this->putJson("/api/alerts/{$alert->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $alert->id,
                    'target_price' => 899.99,
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'id' => $alert->id,
            'target_price' => 899.99,
            'is_active' => false
        ]);
    }

    #[Test]
    public function it_can_delete_alert()
    {
        $alert = PriceAlert::factory()->create();

        $response = $this->deleteJson("/api/alerts/{$alert->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('price_alerts', ['id' => $alert->id]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_alert()
    {
        $response = $this->getJson('/api/alerts/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Alert not found'
            ]);
    }

    #[Test]
    public function it_can_check_if_alert_should_trigger()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        PriceHistory::factory()->for($source)->create([
            'price' => 800.00,
            'scraped_at' => now()
        ]);

        $alert = PriceAlert::factory()->for($product)->create([
            'condition' => 'below',
            'target_price' => 900.00,
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => true,
                'current_price' => 800.00,
                'target_price' => 900.00,
                'condition' => 'below'
            ]);
    }

    #[Test]
    public function it_can_trigger_alert_manually()
    {
        $alert = PriceAlert::factory()->create([
            'trigger_count' => 0,
            'last_triggered_at' => null
        ]);

        $response = $this->postJson("/api/alerts/{$alert->id}/trigger");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Alert triggered successfully',
                'data' => [
                    'trigger_count' => 1
                ]
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'id' => $alert->id,
            'trigger_count' => 1
        ]);

        $alert->refresh();
        $this->assertNotNull($alert->last_triggered_at);
    }

    #[Test]
    public function it_can_get_triggered_alerts_statistics()
    {
        PriceAlert::factory()->triggered()->count(5)->create();
        PriceAlert::factory()->count(3)->create();

        $response = $this->getJson('/api/alerts/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_alerts',
                'active_alerts',
                'triggered_alerts',
                'trigger_rate'
            ]);
    }
}
