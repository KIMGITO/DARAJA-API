<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class PullTransactionsService
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
     * Pull transactions for a shortcode
     * 
     * @param array $data {
     *     start_date: string (Y-m-d),
     *     end_date: string (Y-m-d),
     *     transaction_type?: string (C2B, B2C, etc.),
     *     page?: int,
     *     limit?: int
     * }
     *  @return array|object
     */
    public function query(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'StartDate' => $data['start_date'],
            'EndDate' => $data['end_date'],
            'TransactionType' => $data['transaction_type'] ?? 'C2B',
            'Page' => $data['page'] ?? 1,
            'Limit' => $data['limit'] ?? 100,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['pull_transactions'];

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