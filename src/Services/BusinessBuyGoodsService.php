<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class BusinessBuyGoodsService
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
     * Pay for goods from business account to till number
     * 
     * @param array $data {
     *     required: amount, till_number, account_reference
     *     optional: remarks, initiator, security_credential
     * }
     * @return array|object Payment response
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::businessBuyGoods()->pay([
     *     'amount' => 5000,
     *     'till_number' => '123456',
     *     'account_reference' => 'ORDER-001'
     * ]);
     */
    public function pay(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'Initiator' => $data['initiator'] ?? $this->config['initiator'],
                'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
                'CommandID' => 'BusinessBuyGoods',
                'Amount' => (int) $data['amount'],
                'PartyA' => $this->config['shortcode'],
                'PartyB' => $data['till_number'],
                'AccountReference' => $data['account_reference'],
                'Remarks' => $data['remarks'] ?? 'Goods payment',
                'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
                'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
            ];

            $endpoint = $this->config['endpoints'][$this->config['environment']]['business_buygoods'];

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
            throw new DarajaException('Business Buy Goods failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}