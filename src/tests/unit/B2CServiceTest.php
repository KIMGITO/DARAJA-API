<?php

namespace Codenson\Daraja\Tests\Unit;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Services\B2CService;

class B2CServiceTest extends TestCase
{
    protected $b2cService;
    protected $mockClient;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('daraja');
        $this->authService = Mockery::mock(AuthService::class);
        $this->mockClient = Mockery::mock(Client::class);
        
        $this->b2cService = new B2CService($config, $this->authService);
        
        $reflection = new \ReflectionClass($this->b2cService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->b2cService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_sends_b2c_payment_successfully()
    {
        $this->authService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('access_token_123');

        $mockResponse = new Response(200, [], json_encode([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'ConversationID' => 'conv_123',
            'OriginatorConversationID' => 'orig_123'
        ]));

        $this->mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->b2cService->send([
            'amount' => 1000,
            'phone_number' => '254712345678',
            'remarks' => 'Salary payment'
        ]);

        $this->assertEquals('0', $result['ResponseCode']);
    }
}