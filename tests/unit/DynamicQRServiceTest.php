<?php

namespace Codenson\Daraja\Tests\Unit;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Services\DynamicQRService;

class DynamicQRServiceTest extends TestCase
{
    protected $qrService;
    protected $mockClient;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('daraja');
        $this->authService = Mockery::mock(AuthService::class);
        $this->mockClient = Mockery::mock(Client::class);
        
        $this->qrService = new DynamicQRService($config, $this->authService);
        
        $reflection = new \ReflectionClass($this->qrService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->qrService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_dynamic_qr_code_successfully()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');

        $mockResponse = new Response(200, [], json_encode([
            'ResponseCode' => '0',
            'QRCode' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...',
            'ResponseDescription' => 'Success'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->qrService->generate([
            'merchant_name' => 'Test Store',
            'ref_no' => 'REF-001',
            'amount' => 1500
        ]);

        $this->assertEquals('0', $result['ResponseCode']);
        $this->assertArrayHasKey('QRCode', $result);
    }
}