<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class ReversalService
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
     * Reverse a transaction
     * 
     * @param array $data {
     *     required: transaction_id, amount
     *     optional: receiver_shortcode, remarks, occasion, identifier_type
     * }
     * @return array|object Reversal response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::reversal()->reverse([
     *     'transaction_id' => 'SDF45T56789',
     *     'amount' => 1000,
     *     'remarks' => 'Customer requested reversal'
     * ]);
     */
    public function reverse(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'Initiator' => $data['initiator'] ?? $this->config['initiator'],
                'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
                'CommandID' => 'TransactionReversal',
                'TransactionID' => $data['transaction_id'],
                'Amount' => (int) $data['amount'],
                'ReceiverParty' => $data['receiver_shortcode'] ?? $this->config['shortcode'],
                'RecieverIdentifierType' => $data['identifier_type'] ?? 11,
                'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
                'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
                'Remarks' => $data['remarks'] ?? 'Transaction reversal',
                'Occasion' => $data['occasion'] ?? '',
            ];

            $endpoint = $this->config['endpoints'][$this->config['environment']]['reversal'];

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
            throw new DarajaException('Transaction reversal failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}