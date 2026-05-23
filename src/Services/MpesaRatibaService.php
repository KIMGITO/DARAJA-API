<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class MpesaRatibaService
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
     * Create standing order (M-Pesa Ratiba)
     * 
     * @param array $data {
     *     required: amount, phone_number, start_date, end_date, frequency, account_reference
     *     optional: day_of_month (for monthly), day_of_week (for weekly), remarks
     * }
     * @return array|object Standing order creation response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::mpesaRatiba()->create([
     *     'amount' => 1000,
     *     'phone_number' => '254712345678',
     *     'start_date' => '2025-02-01',
     *     'end_date' => '2025-12-31',
     *     'frequency' => 'MONTHLY',
     *     'day_of_month' => 15,
     *     'account_reference' => 'LOAN-001'
     * ]);
     */
    public function create(array $data)
    {
        try {
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

            $payload = array_filter($payload, function ($value) {
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
        } catch (\Exception $e) {
            throw new DarajaException('Standing order creation failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Cancel standing order
     * 
     * @param string $orderId Standing order ID
     * @return array|object Cancellation response
     * @throws DarajaException
     */
    public function cancel(string $orderId)
    {
        try {
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

            $result = json_decode($response->getBody(), true);

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
        } catch (\Exception $e) {
            throw new DarajaException('Standing order cancellation failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Query standing order status
     * 
     * @param string $orderId Standing order ID
     * @return array|object Standing order status
     * @throws DarajaException
     */
    public function query(string $orderId)
    {
        try {
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

            $result = json_decode($response->getBody(), true);

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
        } catch (\Exception $e) {
            throw new DarajaException('Standing order query failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}