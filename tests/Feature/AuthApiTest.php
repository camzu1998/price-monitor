<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
    }

    #[Test]
    public function it_validates_registration_request()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_prevents_duplicate_email_registration()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);
    }

    #[Test]
    public function it_returns_error_for_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'WrongPassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    #[Test]
    public function it_can_logout_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(204);
        $this->assertCount(0, $user->tokens);
    }

    #[Test]
    public function it_requires_authentication_for_logout()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_get_authenticated_user_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
    }

    #[Test]
    public function it_requires_authentication_for_user_profile()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authentication_for_creating_alerts()
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

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_create_alerts()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $alertData = [
            'product_id' => $product->id,
            'target_price' => 999.99,
            'condition' => 'below',
            'notification_channel' => 'email'
        ];

        $response = $this->postJson('/api/alerts', $alertData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('price_alerts', [
            'product_id' => $product->id,
            'email' => $user->email,
            'target_price' => 999.99
        ]);
    }

    #[Test]
    public function user_can_only_view_own_alerts()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $product = Product::factory()->create();

        PriceAlert::factory()->for($product)->forUser($user1->email)->count(2)->create();
        PriceAlert::factory()->for($product)->forUser($user2->email)->count(3)->create();

        Sanctum::actingAs($user1);

        $response = $this->getJson('/api/alerts');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        foreach ($response->json('data') as $alert) {
            $this->assertEquals($user1->email, $alert['email']);
        }
    }

    #[Test]
    public function user_cannot_update_other_users_alerts()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $product = Product::factory()->create();
        $alert = PriceAlert::factory()->for($product)->forUser($user2->email)->create();

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/alerts/{$alert->id}", [
            'target_price' => 1500.00
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_delete_other_users_alerts()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $product = Product::factory()->create();
        $alert = PriceAlert::factory()->for($product)->forUser($user2->email)->create();

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/alerts/{$alert->id}");

        $response->assertStatus(403);
    }
}
