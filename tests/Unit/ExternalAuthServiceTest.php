<?php

namespace Kristiansnts\FilamentApiLogin\Tests\Unit;

use Kristiansnts\FilamentApiLogin\Services\ExternalAuthService;
use Kristiansnts\FilamentApiLogin\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalAuthServiceTest extends TestCase
{
    private ExternalAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExternalAuthService();
    }

    public function test_successful_authentication_with_token_and_data_format()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([
                'token' => 'test-token-123',
                'data' => [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'username' => 'testuser',
                    'role' => 'admin'
                ]
            ], 200)
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNotNull($result);
        $this->assertEquals('test-token-123', $result['token']);
        $this->assertEquals('test@example.com', $result['data']['email']);
        $this->assertEquals('testuser', $result['data']['username']);
    }

    public function test_successful_authentication_with_flat_format_backwards_compatibility()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([
                'token' => 'test-token-123',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'role' => 'admin'
            ], 200)
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNotNull($result);
        $this->assertEquals('test-token-123', $result['token']);
        $this->assertEquals('testuser', $result['username']);
    }

    public function test_authentication_failure_with_invalid_credentials()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([
                'error' => 'Invalid credentials'
            ], 401)
        ]);

        $result = $this->service->authenticate('test@example.com', 'wrongpassword');

        $this->assertNull($result);
    }

    public function test_authentication_failure_with_server_error()
    {
        Http::fake([
            'test-api.com/auth' => Http::response('Server Error', 500)
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNull($result);
    }

    public function test_authentication_with_network_timeout()
    {
        Http::fake([
            'test-api.com/auth' => function () {
                throw new \Exception('Connection timeout');
            }
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNull($result);
    }

    public function test_authentication_with_malformed_response()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([
                'invalid' => 'response'
            ], 200)
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNotNull($result);
        $this->assertEquals(['invalid' => 'response'], $result);
    }

    public function test_authentication_with_empty_response()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([], 200)
        ]);

        $result = $this->service->authenticate('test@example.com', 'password123');

        $this->assertNull($result);
    }

    public function test_http_request_includes_correct_headers()
    {
        Http::fake([
            'test-api.com/auth' => Http::response([
                'token' => 'test-token',
                'data' => ['id' => 1]
            ], 200)
        ]);

        $this->service->authenticate('test@example.com', 'password123');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Accept', 'application/json') &&
                   $request->url() === 'http://test-api.com/auth' &&
                   $request['email'] === 'test@example.com' &&
                   $request['password'] === 'password123';
        });
    }

    public function test_timeout_configuration_is_respected()
    {
        config(['filament-api-login.timeout' => 5]);
        
        $service = new ExternalAuthService();
        
        Http::fake([
            'test-api.com/auth' => function () {
                sleep(10);
                return Http::response(['token' => 'test'], 200);
            }
        ]);

        $result = $service->authenticate('test@example.com', 'password123');

        $this->assertNull($result);
    }
}