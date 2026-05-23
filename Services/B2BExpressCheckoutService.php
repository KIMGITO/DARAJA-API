<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class B2BExpressCheckoutService
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
     * Initiate B2B Express Checkout (USSD Push to till)
     * 
     * @param array $data {
     *     required: amount, payer_till, payee_till, account_reference
     *     optional: remarks, initiator, security_credential, timeout_url, result_url
     * }
     * @return array|object Response from M-PESA API
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::b2bExpressCheckout()->push([
     *     'amount' => 5000,
     *     'payer_till' => '123456',
     *     'payee_till' => '654321',
     *     'account_reference' => 'TRANS-001',
     *     'remarks' => 'B2B payment'
     * ]);
     */
    public function push(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'Initiator' => $data['initiator'] ?? $this->config['initiator'],
                'SecurityCredential' => $data['security_credential'] ?? $this->config['security_credential'],
                'CommandID' => 'B2BPaymentRequest',
                'Amount' => (int) $data['amount'],
                'PartyA' => $data['payer_till'],
                'PartyB' => $data['payee_till'],
                'AccountReference' => $data['account_reference'],
                'Remarks' => $data['remarks'] ?? 'B2B payment',
                'QueueTimeOutURL' => $data['timeout_url'] ?? $this->config['callback_urls']['reversal'],
                'ResultURL' => $data['result_url'] ?? $this->config['callback_urls']['reversal'],
            ];

            $endpoint = $this->config['endpoints'][$this->config['environment']]['b2b_express_checkout'];

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
            throw new DarajaException('B2B Express Checkout failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}