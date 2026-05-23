<?php

namespace Codenson\Daraja\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Codenson\Daraja\DarajaServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DarajaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('daraja.environment', 'sandbox');
        $app['config']->set('daraja.consumer_key', 'test_consumer_key');
        $app['config']->set('daraja.consumer_secret', 'test_consumer_secret');
        $app['config']->set('daraja.shortcode', '174379');
        $app['config']->set('daraja.passkey', 'test_passkey');
        $app['config']->set('daraja.initiator', 'test_initiator');
        $app['config']->set('daraja.security_credential', 'test_security_credential');
        $app['config']->set('daraja.result_type', 'array');
        
        $app['config']->set('daraja.callback_urls.stk_push', 'https://example.com/stk-callback');
        $app['config']->set('daraja.callback_urls.c2b_confirmation', 'https://example.com/c2b-confirmation');
        $app['config']->set('daraja.callback_urls.c2b_validation', 'https://example.com/c2b-validation');
        $app['config']->set('daraja.callback_urls.b2c_timeout', 'https://example.com/b2c-timeout');
        $app['config']->set('daraja.callback_urls.b2c_result', 'https://example.com/b2c-result');
    }
}