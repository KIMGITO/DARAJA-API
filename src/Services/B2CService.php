<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class B2CService
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
     * Send B2C payment
     * 
     * @param array $data {
     *     required: amount, phone_number
     *     optional: command_id, remarks, occasion, initiator, security_credential
     * }
     * @return array|object Payment response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::b2c()->send([
     *     'amount' => 1000,
     *     'phone_number' => '254712345678',
     *     'command_id' => 'BusinessPayment',
     *     'remarks' => 'Salary payment'
     * ]);
     */
    public function send(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'InitiatorName' => $data['initiator'] ?? $this->config['initiator'],
            'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
            'CommandID' => $data['command_id'] ?? 'BusinessPayment',
            'Amount' => (int) $data['amount'],
            'PartyA' => $data['shortcode'] ?? $this->config['shortcode'],
            'PartyB' => $data['phone_number'],
            'Remarks' => $data['remarks'] ?? 'Payment',
            'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['b2c_timeout'],
            'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['b2c_result'],
            'Occasion' => $data['occasion'] ?? null,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['b2c'];

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