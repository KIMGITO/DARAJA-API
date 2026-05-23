<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class PullTransactionsService
{
    protected Client $client;
    protected array $config;
    protected AuthService $authService;

    public function __construct(array $config, AuthService $authService)
    {
        $this->config = $config;
        $this->authService = $authService;
        $this->client = new Client(['timeout' => $config['timeout'] ?? 30]);
    }

    /**
     * Pull transactions for reconciliation
     * 
     * @param array $data {
     *     required: start_date, end_date
     *     optional: transaction_type, page, limit
     * }
     * @return array|object List of transactions
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::pullTransactions()->query([
     *     'start_date' => '2025-01-01',
     *     'end_date' => '2025-01-31',
     *     'transaction_type' => 'C2B',
     *     'page' => 1,
     *     'limit' => 100
     * ]);
     */
    public function query(array $data)
    {
        try {
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new DarajaException('Pull transactions failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}