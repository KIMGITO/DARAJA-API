<?php

namespace Codenson\Daraja\Services;

use GuzzleHttp\Client;
use Codenson\Daraja\Exceptions\DarajaException;

class IMSIService
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
     * Query IMSI information for SIM verification
     * 
     * @param array{
     *     phone_number: string,
     *     include_imsi?: bool,
     *     include_sim_swap?: bool
     * } $data
     * 
     * @return array|object {
     *     string $MSISDN,                    // Phone number queried
     *     string $IMSI,                      // International Mobile Subscriber Identity (if requested)
     *     string $sim_registration_date,     // Date SIM was registered
     *     string $last_sim_swap_date,        // Date of last SIM swap (if any)
     *     bool $sim_swap_detected,           // Whether SIM was recently swapped
     *     string $response_code,             // Response code from API
     *     string $response_description       // Response description
     * }
     * 
     * @throws DarajaException
     * 
     * @example
     * // Basic IMSI query
     * $response = Daraja::imsi()->query([
     *     'phone_number' => '254712345678'
     * ]);
     * 
     * // Full IMSI query with SIM swap detection
     * $response = Daraja::imsi()->query([
     *     'phone_number' => '254712345678',
     *     'include_imsi' => true,
     *     'include_sim_swap' => true
     * ]);
     */
    public function query(array $data)
    {
        try {
            $accessToken = $this->authService->getAccessToken();
            
            $payload = [
                'MSISDN' => $this->formatPhoneNumber($data['phone_number']),
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

            if (($this->config['result_type'] ?? 'array') === 'object') {
                return (object) $result;
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new DarajaException('IMSI query failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Check if SIM was recently swapped (for security)
     * 
     * @param string $phoneNumber Phone number to check
     * @return array|object {
     *     bool $sim_swap_detected,           // Whether SIM was recently swapped
     *     string $last_sim_swap_date,        // Date of last SIM swap
     *     string $sim_registration_date,     // Original SIM registration date
     *     string $response_code,             // Response code
     *     string $response_description       // Response description
     * }
     * 
     * @throws DarajaException
     * 
     * @example
     * $result = Daraja::imsi()->checkSimSwap('254712345678');
     * 
     * if ($result['sim_swap_detected']) {
     *     // Flag account for additional verification
     *     // Send alert to user
     *     // Temporarily disable sensitive operations
     * }
     */
    public function checkSimSwap(string $phoneNumber)
    {
        return $this->query([
            'phone_number' => $phoneNumber,
            'include_imsi' => false,
            'include_sim_swap' => true,
        ]);
    }

    /**
     * Get only IMSI information without SIM swap data
     * 
     * @param string $phoneNumber Phone number to check
     * @return array|object {
     *     string $IMSI,                      // International Mobile Subscriber Identity
     *     string $MSISDN,                    // Phone number
     *     string $response_code,             // Response code
     *     string $response_description       // Response description
     * }
     * 
     * @throws DarajaException
     * 
     * @example
     * $imsi = Daraja::imsi()->getIMSI('254712345678');
     */
    public function getIMSI(string $phoneNumber)
    {
        return $this->query([
            'phone_number' => $phoneNumber,
            'include_imsi' => true,
            'include_sim_swap' => false,
        ]);
    }

    /**
     * Get SIM registration date
     * 
     * @param string $phoneNumber Phone number to check
     * @return array|object {
     *     string $sim_registration_date,     // Date SIM was registered
     *     string $msisdn_age_days,           // Age of MSISDN in days
     *     string $response_code,             // Response code
     *     string $response_description       // Response description
     * }
     * 
     * @throws DarajaException
     * 
     * @example
     * $info = Daraja::imsi()->getRegistrationDate('254712345678');
     * $daysOld = $info['msisdn_age_days'];
     */
    public function getRegistrationDate(string $phoneNumber)
    {
        $response = $this->query([
            'phone_number' => $phoneNumber,
            'include_imsi' => false,
            'include_sim_swap' => true,
        ]);
        
        // Calculate MSISDN age in days
        if (isset($response['sim_registration_date'])) {
            $regDate = new \DateTime($response['sim_registration_date']);
            $now = new \DateTime();
            $response['msisdn_age_days'] = $regDate->diff($now)->days;
        }
        
        return $response;
    }

    /**
     * Verify if a phone number is valid and active
     * 
     * @param string $phoneNumber Phone number to verify
     * @return array|object {
     *     bool $is_valid,                    // Whether the MSISDN is valid
     *     bool $is_active,                   // Whether the MSISDN is active
     *     string $status,                    // Status description
     *     string $response_code,             // Response code
     *     string $response_description       // Response description
     * }
     * 
     * @throws DarajaException
     * 
     * @example
     * $verification = Daraja::imsi()->verifyMSISDN('254712345678');
     * if ($verification['is_valid']) {
     *     // Proceed with transaction
     * }
     */
    public function verifyMSISDN(string $phoneNumber)
    {
        $response = $this->query([
            'phone_number' => $phoneNumber,
            'include_imsi' => false,
            'include_sim_swap' => false,
        ]);
        
        $result = [
            'is_valid' => false,
            'is_active' => false,
            'status' => 'unknown',
            'response_code' => $response['response_code'] ?? 'unknown',
            'response_description' => $response['response_description'] ?? 'Unknown response',
        ];
        
        // Check if response indicates valid MSISDN
        if (isset($response['response_code']) && $response['response_code'] === '0') {
            $result['is_valid'] = true;
            $result['is_active'] = true;
            $result['status'] = 'active';
        } elseif (isset($response['error_code'])) {
            $result['status'] = 'invalid';
        }
        
        return $result;
    }

    /**
     * Format phone number to required format
     * 
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Remove leading 0 or add 254
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        } elseif (substr($phoneNumber, 0, 4) === '+254') {
            $phoneNumber = '254' . substr($phoneNumber, 4);
        } elseif (substr($phoneNumber, 0, 3) !== '254') {
            $phoneNumber = '254' . $phoneNumber;
        }
        
        return $phoneNumber;
    }
}