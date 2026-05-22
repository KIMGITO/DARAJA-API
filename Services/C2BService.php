<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;

class C2BService
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
     * Register C2B URLs for validation and confirmation
     * @param array $data {
     *     confirmation_url?: string,
     *     validation_url?: string
     * }
     *  @return array|object
     */
    public function registerURLs(?string $confirmationUrl = null, ?string $validationUrl = null)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $confirmationUrl ?? $this->config['callback_urls']['c2b_confirmation'],
            'ValidationURL' => $validationUrl ?? $this->config['callback_urls']['c2b_validation'],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['c2b_register'];

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

    /**
     * Simulate C2B transaction (sandbox only)
     * @param string $phoneNumber
     * @param float $amount
     * @param string $commandId (CustomerPayBillOnline or CustomerBuyGoodsOnline)
      *  @return array|object
     */
    public function simulate(string $phoneNumber, float $amount, string $commandId = 'CustomerPayBillOnline')
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'CommandID' => $commandId,
            'Amount' => (int) $amount,
            'Msisdn' => $phoneNumber,
            'BillRefNumber' => 'Payment',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['c2b_simulate'];

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