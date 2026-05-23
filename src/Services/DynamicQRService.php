<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class DynamicQRService
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
     * Generate dynamic QR code
     * 
     * @param array $data {
     *     required: merchant_name, ref_no, amount
     *     optional: trx_code (BG/PB), cpi, size
     * }
     * @return array|object QR code response with base64 image
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::dynamicQR()->generate([
     *     'merchant_name' => 'My Store',
     *     'ref_no' => 'INV-001',
     *     'amount' => 1500,
     *     'trx_code' => 'BG'
     * ]);
     */
    public function generate(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'MerchantName' => $data['merchant_name'],
            'RefNo' => $data['ref_no'],
            'Amount' => (float) $data['amount'],
            'TrxCode' => $data['trx_code'] ?? 'BG',
            'CPI' => $data['cpi'] ?? $this->config['shortcode'],
            'Size' => $data['size'] ?? '300',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['dynamic_qr'];

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
    }
}