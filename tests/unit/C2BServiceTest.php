<?php

namespace Codenson\Daraja\Tests\Unit;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Services\C2BService;

class C2BServiceTest extends TestCase
{
    protected $c2bService;
    protected $mockClient;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('daraja');
        $this->authService = Mockery::mock(AuthService::class);
        $this->mockClient = Mockery::mock(Client::class);
        
        $this->c2bService = new C2BService($config, $this->authService);
        
        $reflection = new \ReflectionClass($this->c2bService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->c2bService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_registers_c2b_urls_successfully()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');

        $mockResponse = new Response(200, [], json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'success'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->c2bService->registerURLs();

        $this->assertEquals('0', $result['ResponseCode']);
    }

    /** @test */
    public function it_simulates_c2b_transaction_in_sandbox()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');

        $mockResponse = new Response(200, [], json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->c2bService->simulate('254712345678', 100);

        $this->assertEquals('0', $result['ResponseCode']);
    }
}