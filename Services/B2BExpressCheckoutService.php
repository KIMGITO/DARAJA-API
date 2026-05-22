<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class B2BExpressCheckoutService
{
    protected $client;
    protected $config;
    protected $authService;

    public function __construct(array $config, AuthService $authService)
    {
        $this->config = $config;
        $this->authService = $authService;
        $this->client = new Client(['timeout' => $config['timeout'] ?? 30]);
    }

    /**
     * Initiate B2B Express Checkout (USSD Push to till)
     * 
     * @param array $data {
     *     amount: float,
     *     payer_till: string,
     *     payee_till: string,
     *     account_reference: string,
     *     remarks?: string
     * }
     *  @return array|object
     */
    public function push(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => 'B2BPaymentRequest',
            'Amount' => (int) $data['amount'],
            'PartyA' => $data['payer_till'],
            'PartyB' => $data['payee_till'],
            'AccountReference' => $data['account_reference'],
            'Remarks' => $data['remarks'] ?? 'B2B payment',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['b2b_express_checkout'];

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $result = json_decode($response->getBody(), true);

        if ($this->config['result_type'] === 'object') {
            return (object) $result;
        }

        return $result;
    }
}