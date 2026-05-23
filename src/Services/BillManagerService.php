<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class BillManagerService
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
     * Create a bill
     * 
     * @param array $data {
     *     required: customer_name, customer_phone, amount, due_date, bill_reference
     *     optional: description, remarks
     * }
     * @return array|object Bill creation response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::billManager()->createBill([
     *     'customer_name' => 'John Doe',
     *     'customer_phone' => '254712345678',
     *     'amount' => 2500,
     *     'due_date' => '2025-02-15',
     *     'bill_reference' => 'BILL-001'
     * ]);
     */
    public function createBill(array $data)
    {
        try {
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new DarajaException('Bill creation failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update bill status
     * 
     * @param string $billReference Bill reference number
     * @param string $status Status (PAID, PENDING, CANCELLED)
     * @return array|object Update response
     * @throws DarajaException
     */
    public function updateBill(string $billReference, string $status)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'ShortCode' => $this->config['shortcode'],
                'CommandID' => 'UpdateBill',
                'BillReference' => $billReference,
                'Status' => $status,
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new DarajaException('Bill update failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}