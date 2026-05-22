<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class LipaNaBongaService
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
     * Pay with Bonga Points
     * 
     * @param array $data {
     *     amount: float,
     *     phone_number: string,
     *     bonga_points: int,
     *     till_number: string,
     *     account_reference: string
     * }
     *  @return array|object
     */
    public function pay(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Amount' => (int) $data['amount'],
            'PhoneNumber' => $data['phone_number'],
            'BongaPoints' => (int) $data['bonga_points'],
            'TillNumber' => $data['till_number'] ?? $this->config['till_number'],
            'AccountReference' => $data['account_reference'],
            'TransactionDesc' => $data['description'] ?? 'Payment with Bonga Points',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['lipa_na_bonga'];

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
     * Check Bonga Points balance
     */
    public function checkBalance(string $phoneNumber): array
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'PhoneNumber' => $phoneNumber,
            'Command' => 'CheckBalance',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['lipa_na_bonga'] . '/balance';

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody(), true);
    }
}