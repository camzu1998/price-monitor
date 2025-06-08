<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Product;
use App\Models\ProductSource;
use App\Models\PriceHistory;
use App\Models\PriceAlert;
use App\Services\AlertService;
use App\Services\AlertTrigger\AlertTriggerStrategyFactory;
use App\Enums\AlertCondition;
use App\Enums\NotificationChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\App\Providers\AlertServiceProvider::class);
    }

    #[Test]
    public function it_can_create_alert_below_price()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'target_price' => 999.99,
            'condition' => AlertCondition::BELOW->value,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'product_id' => $product->id,
                'email' => 'test@example.com',
                'target_price' => "999.99",
                'condition' => AlertCondition::BELOW->value,
                'is_active' => true
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::BELOW->value,
            'target_price' => 999.99
        ]);
    }

    #[Test]
    public function it_can_create_alert_percent_drop()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::PERCENT_DROP->value,
            'target_price' => 0,
            'percent_threshold' => 15.0,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'condition' => AlertCondition::PERCENT_DROP->value,
                    'percent_threshold' => 15.0
                ]
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'product_id' => $product->id,
            'condition' => AlertCondition::PERCENT_DROP->value,
            'percent_threshold' => 15.0
        ]);
    }

    #[Test]
    public function it_validates_invalid_condition()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => 'invalid_condition',
            'target_price' => 999.99,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['condition']);
    }

    #[Test]
    public function it_validates_missing_target_price_for_below_condition()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::BELOW->value,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_price']);
    }

    #[Test]
    public function it_validates_missing_percent_threshold_for_percent_drop()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::PERCENT_DROP->value,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['percent_threshold']);
    }

    #[Test]
    public function it_validates_invalid_email()
    {
        $product = Product::factory()->create();

        $alertData = [
            'product_id' => $product->id,
            'email' => 'invalid-email',
            'target_price' => 999.99,
            'condition' => AlertCondition::BELOW->value,
            'notification_channel' => NotificationChannel::EMAIL->value
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_update_alert()
    {
        $product = Product::factory()->create();

        $alert = PriceAlert::create([
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::BELOW->value,
            'target_price' => 1000.00,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true
        ]);

        $updateData = ['target_price' => 1500.00];

        $response = $this->putJson("/api/alerts/{$alert->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'target_price' => "1500.00"
                ]
            ]);

        $this->assertDatabaseHas('price_alerts', [
            'id' => $alert->id,
            'target_price' => 1500.00
        ]);
    }

    #[Test]
    public function it_can_delete_alert()
    {
        $product = Product::factory()->create();

        $alert = PriceAlert::create([
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'condition' => AlertCondition::BELOW->value,
            'target_price' => 1000.00,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true
        ]);

        $response = $this->deleteJson("/api/alerts/{$alert->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('price_alerts', [
            'id' => $alert->id
        ]);
    }

    #[Test]
    public function it_can_get_alerts_for_user()
    {
        $product = Product::factory()->create();
        $userEmail = 'user@example.com';

        // Create 3 alerts for our user
        PriceAlert::factory()
            ->count(3)
            ->for($product)
            ->forUser($userEmail)
            ->create();

        // Create 2 alerts for different user
        PriceAlert::factory()
            ->count(2)
            ->for($product)
            ->forUser('other@example.com')
            ->create();

        $response = $this->getJson("/api/alerts?email={$userEmail}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_evaluates_below_price_condition_true()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        // Clean, readable factory usage with states
        PriceHistory::factory()
            ->for($source)
            ->withPrices(current: 800.00, previous: 900.00)
            ->available()
            ->create();

        $alert = PriceAlert::factory()
            ->for($product)
            ->belowPrice(900.00)
            ->forUser('test@example.com')
            ->create();

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => true,
                'current_price' => 800.00,
                'target_price' => 900.00,
                'condition' => AlertCondition::BELOW->value
            ]);
    }

    #[Test]
    public function it_evaluates_below_price_condition_false()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        PriceHistory::factory()
            ->for($source)
            ->withPrices(current: 1200.00, previous: 1100.00)
            ->available()
            ->create();

        $alert = PriceAlert::factory()
            ->for($product)
            ->belowPrice(900.00)
            ->forUser('test@example.com')
            ->create();

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => false,
                'current_price' => 1200.00,
                'target_price' => 900.00,
                'condition' => AlertCondition::BELOW->value
            ]);
    }

    #[Test]
    public function it_evaluates_above_price_condition_true()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        PriceHistory::factory()
            ->for($source)
            ->withPrices(current: 1200.00, previous: 1000.00)
            ->available()
            ->create();

        $alert = PriceAlert::factory()
            ->for($product)
            ->abovePrice(1000.00)
            ->forUser('test@example.com')
            ->create();

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => true,
                'current_price' => 1200.00,
                'target_price' => 1000.00,
                'condition' => AlertCondition::ABOVE->value
            ]);
    }

    #[Test]
    public function it_evaluates_percent_drop_condition_true()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        // EXPLICIT data - 20% drop (1000 -> 800)
        PriceHistory::create([
            'product_source_id' => $source->id,
            'price' => 800.00,
            'previous_price' => 1000.00, // Explicit previous price for 20% drop
            'currency' => 'PLN',
            'is_available' => true,
            'scraped_at' => now()
        ]);

        $alert = PriceAlert::create([
            'product_id' => $product->id,
            'condition' => AlertCondition::PERCENT_DROP->value,
            'percent_threshold' => 15.0,
            'target_price' => 0,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true,
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => true,
                'current_price' => 800.00,
                'condition' => AlertCondition::PERCENT_DROP->value
            ]);
    }

    #[Test]
    public function it_evaluates_percent_drop_condition_false_no_previous_price()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        // EXPLICIT data - NO previous_price
        PriceHistory::create([
            'product_source_id' => $source->id,
            'price' => 800.00,
            'previous_price' => null, // Explicitly null
            'currency' => 'PLN',
            'is_available' => true,
            'scraped_at' => now()
        ]);

        $alert = PriceAlert::create([
            'product_id' => $product->id,
            'condition' => AlertCondition::PERCENT_DROP->value,
            'percent_threshold' => 15.0,
            'target_price' => 0,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true,
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => false,
                'current_price' => 800.00,
                'condition' => AlertCondition::PERCENT_DROP->value
            ]);
    }

    #[Test]
    public function it_evaluates_percent_increase_condition_true()
    {
        $product = Product::factory()->create();
        $source = ProductSource::factory()->for($product)->create();

        // EXPLICIT data with guaranteed previous_price
        PriceHistory::create([
            'product_source_id' => $source->id,
            'price' => 1000.00,
            'previous_price' => 800.00, // Explicit previous price for 25% increase
            'currency' => 'PLN',
            'is_available' => true,
            'scraped_at' => now()
        ]);

        $alert = PriceAlert::create([
            'product_id' => $product->id,
            'condition' => AlertCondition::PERCENT_INCREASE->value,
            'percent_threshold' => 20.0,
            'target_price' => 0,
            'notification_channel' => NotificationChannel::EMAIL->value,
            'is_active' => true,
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson("/api/alerts/{$alert->id}/should-trigger");

        $response->assertStatus(200)
            ->assertJson([
                'should_trigger' => true,
                'current_price' => 1000.00,
                'condition' => AlertCondition::PERCENT_INCREASE->value
            ]);
    }

    #[Test]
    public function it_returns_alert_statistics()
    {
        $product = Product::factory()->create();

        // Create 3 alerts with 0 triggers
        collect(range(1, 3))->each(function() use ($product) {
            PriceAlert::create([
                'product_id' => $product->id,
                'email' => 'test@example.com',
                'condition' => AlertCondition::BELOW->value,
                'target_price' => 1000.00,
                'notification_channel' => NotificationChannel::EMAIL->value,
                'is_active' => true,
                'trigger_count' => 0
            ]);
        });

        // Create 2 alerts with 5 triggers each
        collect(range(1, 2))->each(function() use ($product) {
            PriceAlert::create([
                'product_id' => $product->id,
                'email' => 'test@example.com',
                'condition' => AlertCondition::BELOW->value,
                'target_price' => 1000.00,
                'notification_channel' => NotificationChannel::EMAIL->value,
                'is_active' => true,
                'trigger_count' => 5
            ]);
        });

        $response = $this->getJson('/api/alerts/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_alerts',
                'active_alerts',
                'triggered_alerts',
                'trigger_rate',
                'average_triggers_per_alert'
            ])
            ->assertJson([
                'total_alerts' => 5,
                'triggered_alerts' => 2,
                'trigger_rate' => 40.0
            ]);
    }

    #[Test]
    public function it_validates_alert_service_provider_registration()
    {
        $alertService = app(AlertService::class);
        $this->assertInstanceOf(AlertService::class, $alertService);

        $strategyFactory = app(AlertTriggerStrategyFactory::class);
        $this->assertInstanceOf(AlertTriggerStrategyFactory::class, $strategyFactory);

        $validConditions = $strategyFactory->getAvailableConditions();
        $this->assertContains(AlertCondition::BELOW->value, $validConditions);
        $this->assertContains(AlertCondition::ABOVE->value, $validConditions);
        $this->assertContains(AlertCondition::PERCENT_DROP->value, $validConditions);
        $this->assertContains(AlertCondition::PERCENT_INCREASE->value, $validConditions);
    }

    #[Test]
    public function it_validates_strategy_creation_with_enums()
    {
        $strategyFactory = app(AlertTriggerStrategyFactory::class);

        $belowStrategy = $strategyFactory->create(AlertCondition::BELOW->value);
        $this->assertEquals(AlertCondition::BELOW->value, $belowStrategy->getConditionName());

        $aboveStrategy = $strategyFactory->create(AlertCondition::ABOVE->value);
        $this->assertEquals(AlertCondition::ABOVE->value, $aboveStrategy->getConditionName());

        $percentDropStrategy = $strategyFactory->create(AlertCondition::PERCENT_DROP->value);
        $this->assertEquals(AlertCondition::PERCENT_DROP->value, $percentDropStrategy->getConditionName());

        $percentIncreaseStrategy = $strategyFactory->create(AlertCondition::PERCENT_INCREASE->value);
        $this->assertEquals(AlertCondition::PERCENT_INCREASE->value, $percentIncreaseStrategy->getConditionName());
    }
}
