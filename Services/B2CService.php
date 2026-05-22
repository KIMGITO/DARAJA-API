<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;

class B2CService
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
     * Send B2C payment
     * @param array $data {
     *     amount: float,
     *     phone_number: string,
     *     command_id: string (SalaryPayment, BusinessPayment, PromotionPayment),
     *     remarks?: string,
     *     timeout_url?: string,
     *     result_url?: string,
     *     occasion?: string
     * }
     *  @return array|object
     */
    public function send(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'InitiatorName' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => $data['command_id'] ?? 'BusinessPayment', // SalaryPayment, BusinessPayment, PromotionPayment
            'Amount' => (int) $data['amount'],
            'PartyA' => $data['shortcode'] ?? $this->config['shortcode'],
            'PartyB' => $data['phone_number'],
            'Remarks' => $data['remarks'] ?? 'Payment',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['b2c_timeout'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['b2c_result'],
            'Occasion' => $data['occasion'] ?? null,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['b2c'];

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