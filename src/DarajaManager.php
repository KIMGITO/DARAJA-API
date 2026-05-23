<?php

namespace Codenson\Daraja;

use Codenson\Daraja\Services\AuthService;
use Codenson\Daraja\Services\STKPushService;
use Codenson\Daraja\Services\DynamicQRService;

class DarajaManager
{
    public function auth(): AuthService
    {
        return app(AuthService::class);
    }

    public function stkPush(): STKPushService
    {
        return app(STKPushService::class);
    }

    public function dynamicQR(): DynamicQRService
    {
        return app(DynamicQRService::class);
    }
}