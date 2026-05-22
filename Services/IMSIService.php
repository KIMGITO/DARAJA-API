<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class IMSIService
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
     * Query IMSI information for SIM verification
     * 
     * @param array $data {
     *     phone_number: string,
     *     include_imsi?: bool,
     *     include_sim_swap?: bool
     * }
     *  @return array|object
     */
    public function query(array $data)
    {
        $accessToken = $this->authService->getAccessToken();
        
        $payload = [
            'MSISDN' => $data['phone_number'],
            'IncludeIMSI' => $data['include_imsi'] ?? true,
            'IncludeSimSwap' => $data['include_sim_swap'] ?? true,
        ];

        $endpoint = $this->config['endpoints'][$this->config['environment']]['imsi'];

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

    /**
     * Check if SIM was recently swapped (for security)
     */
    public function checkSimSwap(string $phoneNumber): array
    {
        return $this->query([
            'phone_number' => $phoneNumber,
            'include_imsi' => false,
            'include_sim_swap' => true,
        ]);
    }
}