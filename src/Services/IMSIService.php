<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class B2PochiService
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
     * Send payment to Pochi La Biashara (Micro SME wallet)
     * 
     * @param array $data {
     *     required: amount, phone_number, pochi_number
     *     optional: remarks, initiator, security_credential
     * }
     * @return array|object Payment response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::b2Pochi()->send([
     *     'amount' => 5000,
     *     'phone_number' => '254712345678',
     *     'pochi_number' => 'POCHI123456'
     * ]);
     */
    public function send(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'Initiator' => $data['initiator'] ?? $this->config['initiator'],
                'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
                'CommandID' => 'BusinessToPochi',
                'Amount' => (int) $data['amount'],
                'PartyA' => $this->config['shortcode'],
                'PartyB' => $data['phone_number'],
                'PochiNumber' => $data['pochi_number'],
                'Remarks' => $data['remarks'] ?? 'Payment to Pochi',
                'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['b2c_timeout'],
                'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['b2c_result'],
            ];

            $endpoint = $this->config['endpoints'][$this->config['environment']]['b2_pochi'];

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
            throw new DarajaException('B2 Pochi payment failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}