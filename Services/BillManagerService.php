<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class BillManagerService
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
     * Create bill (utility bills, invoices, etc.)
     * 
     * @param array $data {
     *     customer_name: string,
     *     customer_phone: string,
     *     amount: float,
     *     due_date: string (Y-m-d),
     *     bill_reference: string,
     *     description?: string
     * }
     *  @return array|object
     */
    public function createBill(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'CommandID' => 'CreateBill',
            'CustomerName' => $data['customer_name'],
            'CustomerPhone' => $data['customer_phone'],
            'Amount' => (int) $data['amount'],
            'DueDate' => $data['due_date'],
            'BillReference' => $data['bill_reference'],
            'Description' => $data['description'] ?? 'Bill payment',
            'Remarks' => $data['remarks'] ?? '',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['bill_manager'];

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
     * Update bill status
     */
    public function updateBill(string $billReference, string $status): array
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'ShortCode' => $this->config['shortcode'],
            'CommandID' => 'UpdateBill',
            'BillReference' => $billReference,
            'Status' => $status, // PAID, PENDING, CANCELLED
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['bill_manager'];

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