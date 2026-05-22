<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class MpesaRatibaService
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
     * Create standing order (M-Pesa Ratiba)
     * 
     * @param array $data {
     *     amount: float,
     *     phone_number: string,
     *     start_date: string (Y-m-d),
     *     end_date: string (Y-m-d),
     *     frequency: string (DAILY, WEEKLY, MONTHLY),
     *     day_of_month?: int (1-31 for monthly),
     *     day_of_week?: string (MON, TUE, etc. for weekly),
     *     account_reference: string
     * }
     *  @return array|object
     */
    public function create(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'Amount' => (int) $data['amount'],
            'PhoneNumber' => $data['phone_number'],
            'StartDate' => $data['start_date'],
            'EndDate' => $data['end_date'],
            'Frequency' => $data['frequency'],
            'DayOfMonth' => $data['day_of_month'] ?? null,
            'DayOfWeek' => $data['day_of_week'] ?? null,
            'AccountReference' => $data['account_reference'],
            'Remarks' => $data['remarks'] ?? 'Standing order',
            'CallBackURL' => $data['callback_url'] ?? $this->config['callback_urls']['stk_push'],
        ];

        // Remove null values
        $payload = array_filter($payload, function($value) {
            return !is_null($value);
        });

        $endpoint = $this->config['endpoints'][$this->config['environment']]['mpesa_ratiba'];

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
     * Cancel standing order
     */
    public function cancel(string $orderId): array
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'OrderID' => $orderId,
            'Command' => 'CANCEL',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['mpesa_ratiba'] . '/cancel';

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Query standing order status
     */
    public function query(string $orderId): array
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'OrderID' => $orderId,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['mpesa_ratiba'] . '/query';

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