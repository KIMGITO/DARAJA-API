<?php

namespace Codenson\Daraja\Tests\Unit;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Services\STKPushService;

class STKPushServiceTest extends TestCase
{
    protected $stkService;
    protected $mockClient;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('daraja');
        $this->authService = Mockery::mock(AuthService::class);
        $this->mockClient = Mockery::mock(Client::class);
        
        $this->stkService = new STKPushService($config, $this->authService);
        
        $reflection = new \ReflectionClass($this->stkService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->stkService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_sends_stk_push_request_successfully()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');
        
        $this->authService->shouldReceive('getTimestamp')
            ->once()
            ->andReturn('20250101120000');
        
        $this->authService->shouldReceive('generatePassword')
            ->once()
            ->andReturn('encoded_password');

        $mockResponse = new Response(200, [], json_encode([
            'MerchantRequestID' => '12345',
            'CheckoutRequestID' => 'ws_CO_123456789',
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success. Request accepted for processing',
            'CustomerMessage' => 'Success. Request accepted for processing'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->stkService->request([
            'amount' => 10,
            'phone_number' => '254712345678',
            'account_reference' => 'INV-001',
            'transaction_desc' => 'Payment for goods'
        ]);

        $this->assertEquals('0', $result['ResponseCode']);
        $this->assertEquals('ws_CO_123456789', $result['CheckoutRequestID']);
    }

    /** @test */
    public function it_queries_stk_push_status_successfully()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');
        
        $this->authService->shouldReceive('getTimestamp')
            ->once()
            ->andReturn('20250101120000');
        
        $this->authService->shouldReceive('generatePassword')
            ->once()
            ->andReturn('encoded_password');

        $mockResponse = new Response(200, [], json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'ResultCode' => '0',
            'ResultDesc' => 'The service request has been accepted successfully'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->stkService->query('ws_CO_123456789');

        $this->assertEquals('0', $result['ResponseCode']);
    }
}