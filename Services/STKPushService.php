<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class STKPushService
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
     * Initiate STK Push (Lipa Na M-PESA Online)
     * 
     * @param array $data {
     *     required: amount, phone_number, account_reference, transaction_desc
     *     optional: transaction_type, callback_url, shortcode, passkey
     * }
     * @return array|object Response from M-PESA API
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::stkPush()->request([
     *     'amount' => 10,
     *     'phone_number' => '254712345678',
     *     'account_reference' => 'INV-001',
     *     'transaction_desc' => 'Payment for goods'
     * ]);
     */
    public function request(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        $timestamp = $this->authService->getTimestamp();
        
        $shortcode = $data['shortcode'] ?? $this->config['shortcode'];
        $passkey = $data['passkey'] ?? $this->config['passkey'];
        
        $password = $this->authService->generatePassword($shortcode, $passkey, $timestamp);
        
        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $data['transaction_type'] ?? 'CustomerPayBillOnline',
            'Amount' => (int) $data['amount'],
            'PartyA' => $data['phone_number'],
            'PartyB' => $shortcode,
            'PhoneNumber' => $data['phone_number'],
            'CallBackURL' => $data['callback_url'] ?? $this->config['callback_urls']['stk_push'],
            'AccountReference' => $data['account_reference'],
            'TransactionDesc' => $data['transaction_desc'],
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['stk_push'];

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

    /**
     * Query STK Push status
     * 
     * @param string $checkoutRequestID The CheckoutRequestID from stkPush request
     * @return array|object Transaction status
     * @throws DarajaException
     * 
     * @example
     * $response = Daraja::stkPush()->query('ws_CO_123456789');
     */
    public function query(string $checkoutRequestID)
    {
        $accessToken = $this->authService->getAccessToken();
        $timestamp = $this->authService->getTimestamp();
        
        $shortcode = $this->config['shortcode'];
        $passkey = $this->config['passkey'];
        
        $password = $this->authService->generatePassword($shortcode, $passkey, $timestamp);
        
        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestID,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['stk_query'];

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