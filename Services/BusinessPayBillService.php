<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class BusinessPayBillService
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
     * Pay bills from business account to paybill number
     * 
     * @param array $data {
     *     amount: float,
     *     paybill_number: string,
     *     account_reference: string,
     *     remarks?: string
     * }
     *  @return array|object
     */
    public function pay(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => 'BusinessPayBill',
            'Amount' => (int) $data['amount'],
            'PartyA' => $this->config['shortcode'],
            'PartyB' => $data['paybill_number'],
            'AccountReference' => $data['account_reference'],
            'Remarks' => $data['remarks'] ?? 'Bill payment',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['business_paybill'];

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