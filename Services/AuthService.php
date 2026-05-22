<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Codenson\Daraja\Exceptions\DarajaException;

class AuthService
{
    protected $client;
    protected $config;
    protected $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client(['timeout' => $config['timeout'] ?? 30]);
        $this->baseUrl = $config['endpoints'][$config['environment']]['auth'];
    }

    /**
     * Get OAuth access token
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'daraja_access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->client->get($this->baseUrl, [
            'auth' => [
                $this->config['consumer_key'],
                $this->config['consumer_secret']
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['access_token'])) {
            throw new DarajaException('Failed to get access token: ' . json_encode($data));
        }

        // Cache token for 50 minutes (expires in 1 hour)
        Cache::put($cacheKey, $data['access_token'], 3000);

        return $data['access_token'];
    }

    /**
     * Generate password for STK Push
     */
    public function generatePassword(string $shortcode, string $passkey, string $timestamp): string
    {
        return base64_encode($shortcode . $passkey . $timestamp);
    }

    /**
     * Get formatted timestamp
     */
    public function getTimestamp(): string
    {
        return date('YmdHis');
    }
}