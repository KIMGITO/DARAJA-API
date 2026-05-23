<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class TaxRemittanceService
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
     * Remit tax to KRA
     * 
     * @param array $data {
     *     required: amount, payer_number, pin, period
     *     optional: remarks, initiator, security_credential
     * }
     * @return array|object Tax remittance response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::taxRemittance()->remit([
     *     'amount' => 5000,
     *     'payer_number' => '254712345678',
     *     'pin' => 'A001234567',
     *     'period' => '2025-01'
     * ]);
     */
    public function remit(array $data)
    {
        try {
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new DarajaException('Tax remittance failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}