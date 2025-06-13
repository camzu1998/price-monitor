<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_access_swagger_documentation_ui()
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertSee('swagger-ui')
            ->assertSee('Price Monitor API');
    }

    #[Test]
    public function it_can_access_swagger_json_documentation()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'openapi',
                'info' => [
                    'title',
                    'description',
                    'version'
                ],
                'servers',
                'paths',
                'components'
            ]);
    }

    #[Test]
    public function it_contains_all_api_endpoints_in_documentation()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);

        $documentation = $response->json();
        $paths = array_keys($documentation['paths']);

        $this->assertContains('/api/auth/register', $paths);
        $this->assertContains('/api/auth/login', $paths);
        $this->assertContains('/api/auth/logout', $paths);
        $this->assertContains('/api/auth/user', $paths);

        $this->assertContains('/api/products', $paths);
        $this->assertContains('/api/products/{id}', $paths);
        $this->assertContains('/api/products/{product}/price-history', $paths);

        $this->assertContains('/api/alerts', $paths);
        $this->assertContains('/api/alerts/{alert}', $paths);
        $this->assertContains('/api/alerts/statistics', $paths);
    }

    #[Test]
    public function it_includes_authentication_security_scheme()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200)
            ->assertJsonPath('components.securitySchemes.sanctum.type', 'apiKey')
            ->assertJsonPath('components.securitySchemes.sanctum.in', 'header')
            ->assertJsonPath('components.securitySchemes.sanctum.name', 'Authorization');
    }

    #[Test]
    public function it_includes_all_request_and_response_schemas()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);

        $schemas = array_keys($response->json('components.schemas'));

        $this->assertContains('CreateAlertRequest', $schemas);
        $this->assertContains('UpdateAlertRequest', $schemas);
        $this->assertContains('LoginRequest', $schemas);
        $this->assertContains('RegisterRequest', $schemas);
        $this->assertContains('ProductSearchRequest', $schemas);

        $this->assertContains('ProductResource', $schemas);
        $this->assertContains('AlertResource', $schemas);
        $this->assertContains('PriceHistoryResource', $schemas);
        $this->assertContains('UserResource', $schemas);

        $this->assertContains('ErrorResponse', $schemas);
        $this->assertContains('ValidationErrorResponse', $schemas);
    }

    #[Test]
    public function it_documents_all_http_methods_correctly()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);

        $documentation = $response->json();

        $this->assertArrayHasKey('get', $documentation['paths']['/api/products']);
        $this->assertArrayHasKey('get', $documentation['paths']['/api/products/{id}']);

        $this->assertArrayHasKey('post', $documentation['paths']['/api/auth/register']);
        $this->assertArrayHasKey('post', $documentation['paths']['/api/alerts']);

        $this->assertArrayHasKey('put', $documentation['paths']['/api/alerts/{alert}']);

        $this->assertArrayHasKey('delete', $documentation['paths']['/api/alerts/{alert}']);
    }

    #[Test]
    public function it_includes_proper_response_codes_documentation()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);

        $documentation = $response->json();

        $loginResponses = $documentation['paths']['/api/auth/login']['post']['responses'];
        $this->assertArrayHasKey('200', $loginResponses);
        $this->assertArrayHasKey('401', $loginResponses);
        $this->assertArrayHasKey('422', $loginResponses);

        $alertResponses = $documentation['paths']['/api/alerts']['post']['responses'];
        $this->assertArrayHasKey('201', $alertResponses);
        $this->assertArrayHasKey('401', $alertResponses);
        $this->assertArrayHasKey('422', $alertResponses);
    }

    #[Test]
    public function it_validates_openapi_specification()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);

        $documentation = $response->json();

        $this->assertMatchesRegularExpression('/^3\.0\.\d+$/', $documentation['openapi']);
        $this->assertIsArray($documentation['info']);
        $this->assertIsArray($documentation['paths']);
        $this->assertIsArray($documentation['components']);
        $this->assertIsArray($documentation['servers']);
    }
}
