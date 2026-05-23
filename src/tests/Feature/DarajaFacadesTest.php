<?php

namespace Codenson\Daraja\Tests\Feature;

use Codenson\Daraja\Tests\TestCase;
use Codenson\Daraja\Facades\Daraja;
use Codenson\Daraja\Services\STKPushService;
use Codenson\Daraja\Services\C2BService;
use Codenson\Daraja\Services\B2CService;

class DarajaFacadeTest extends TestCase
{
    /** @test */
    public function it_resolves_stk_push_service()
    {
        $service = Daraja::stkPush();
        
        $this->assertInstanceOf(STKPushService::class, $service);
    }

    /** @test */
    public function it_resolves_c2b_service()
    {
        $service = Daraja::c2b();
        
        $this->assertInstanceOf(C2BService::class, $service);
    }

    /** @test */
    public function it_resolves_b2c_service()
    {
        $service = Daraja::b2c();
        
        $this->assertInstanceOf(B2CService::class, $service);
    }

    /** @test */
    public function helper_function_returns_daraja_instance()
    {
        $daraja = daraja();
        
        $this->assertInstanceOf(\Codenson\Daraja\Daraja::class, $daraja);
    }
}