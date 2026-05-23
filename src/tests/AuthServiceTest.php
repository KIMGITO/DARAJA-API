<?php

namespace Codenson\Daraja\Tests\Unit;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Exceptions\DarajaException;

class AuthServiceTest extends TestCase
{
    protected $authService;
    protected $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('daraja');
        $this->mockClient = Mockery::mock(Client::class);
        $this->authService = new AuthService($config);
        
        // Replace the client with mock
        $reflection = new \ReflectionClass($this->authService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->authService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_gets_access_token_from_cache()
    {
        Cache::shouldReceive('has')
            ->once()
            ->with('daraja_access_token')
            ->andReturn(true);
        
        Cache::shouldReceive('get')
            ->once()
            ->with('daraja_access_token')
            ->andReturn('cached_token_123');

        $token = $this->authService->getAccessToken();
        
        $this->assertEquals('cached_token_123', $token);
    }

    /** @test */
    public function it_fetches_new_access_token_when_not_cached()
    {
        Cache::shouldReceive('has')
            ->once()
            ->with('daraja_access_token')
            ->andReturn(false);
        
        Cache::shouldReceive('put')
            ->once()
            ->with('daraja_access_token', 'new_token_456', 3000);
        
        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'new_token_456',
            'expires_in' => 3599
        ]));
        
        $this->mockClient->shouldReceive('get')
            ->once()
            ->andReturn($mockResponse);

        $token = $this->authService->getAccessToken();
        
        $this->assertEquals('new_token_456', $token);
    }

    /** @test */
    public function it_throws_exception_when_access_token_not_returned()
    {
        Cache::shouldReceive('has')->andReturn(false);
        Cache::shouldReceive('put');
        
        $mockResponse = new Response(200, [], json_encode([
            'error' => 'Invalid credentials'
        ]));
        
        $this->mockClient->shouldReceive('get')
            ->once()
            ->andReturn($mockResponse);

        $this->expectException(DarajaException::class);
        
        $this->authService->getAccessToken();
    }

    /** @test */
    public function it_generates_password_correctly()
    {
        $shortcode = '174379';
        $passkey = 'test_passkey';
        $timestamp = '20250101120000';
        
        $expected = base64_encode($shortcode . $passkey . $timestamp);
        $result = $this->authService->generatePassword($shortcode, $passkey, $timestamp);
        
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_returns_timestamp_in_correct_format()
    {
        $timestamp = $this->authService->getTimestamp();
        
        $this->assertMatchesRegularExpression('/^\d{14}$/', $timestamp);
    }
}