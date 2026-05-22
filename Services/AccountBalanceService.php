<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class AccountBalanceService
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
     * Query account balance
     * 
     * @param array $data {
     *     shortcode?: string,
     *     identifier_type?: int (1=MSISDN,2=Till,3=Shortcode,4=Organization),
     *     remarks?: string
     * }
     *  @return array|object
     */
    public function query(array $data = [])
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => 'AccountBalance',
            'PartyA' => $data['shortcode'] ?? $this->config['shortcode'],
            'IdentifierType' => $data['identifier_type'] ?? 4,
            'Remarks' => $data['remarks'] ?? 'Account balance query',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['account_balance'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['account_balance'],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['account_balance'];

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