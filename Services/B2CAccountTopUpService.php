<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class B2CAccountTopUpService
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
     * Load funds to B2C shortcode for disbursement
     * 
     * @param array $data {
     *     required: amount, reference
     *     optional: remarks, initiator, security_credential
     * }
     * @return array|object Top-up response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::b2cAccountTopUp()->topUp([
     *     'amount' => 50000,
     *     'reference' => 'TOPUP-001',
     *     'remarks' => 'Monthly fund loading'
     * ]);
     */
    public function topUp(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'Initiator' => $data['initiator'] ?? $this->config['initiator'],
                'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
                'CommandID' => 'AccountTopUp',
                'Amount' => (int) $data['amount'],
                'PartyA' => $this->config['shortcode'],
                'Reference' => $data['reference'],
                'Remarks' => $data['remarks'] ?? 'Account top up',
                'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['b2c_timeout'],
                'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['b2c_result'],
            ];

            $endpoint = $this->config['endpoints'][$this->config['environment']]['b2c_account_topup'];

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
            throw new DarajaException('B2C Account Top Up failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}