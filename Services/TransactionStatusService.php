<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class TransactionStatusService
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
     * Query transaction status
     * 
     * @param array $data {
     *     transaction_id: string (M-PESA receipt number),
     *     remarks: string,
     *     occasion?: string,
     *     identifier_type?: int (1=MSISDN,2=Till,3=Shortcode,4=Organization)
     * }
     *  @return array|object
     */
    public function query(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $data['transaction_id'],
            'PartyA' => $data['party_a'] ?? $this->config['shortcode'],
            'IdentifierType' => $data['identifier_type'] ?? 4,
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['transaction_status'],
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['transaction_status'],
            'Remarks' => $data['remarks'] ?? 'Transaction status query',
            'Occasion' => $data['occasion'] ?? '',
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['transaction_status'];

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