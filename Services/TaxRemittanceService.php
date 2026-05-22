<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class TaxRemittanceService
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
     * Remit tax to KRA
     * 
     * @param array $data {
     *     amount: float,
     *     payer_number: string,
     *     pin: string (KRA PIN),
     *     period: string (e.g., '2025-01'),
     *     remarks?: string
     * }
     *  @return array|object
     */
    public function remit(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => 'TaxRemittance',
            'Amount' => (int) $data['amount'],
            'PartyA' => $this->config['shortcode'],
            'PartyB' => $data['payer_number'],
            'Remarks' => $data['remarks'] ?? 'Tax remittance',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
            'Tax' => [
                'PIN' => $data['pin'],
                'Period' => $data['period'],
            ],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['tax_remittance'];

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