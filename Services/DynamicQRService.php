<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;

class DynamicQRService
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
     * Generate dynamic QR code
     * @param array $data {
     *     merchant_name: string,
     *     ref_no: string,
     *     amount: float,
     *     trx_code?: string (BG=Buy Goods, PB=Paybill),
     *     cpi?: string (default: shortcode),
     *     size?: string (default: 300)
     * }
      *  @return array|object
     */
    public function generate(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'MerchantName' => $data['merchant_name'],
            'RefNo' => $data['ref_no'],
            'Amount' => (float) $data['amount'],
            'TrxCode' => $data['trx_code'] ?? 'BG', // BG - Buy Goods, PB - Paybill
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

        if ($this->config['result_type'] === 'object') {
            return (object) $result;
        }

        return $result;
    }
}