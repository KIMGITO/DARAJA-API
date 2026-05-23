<?php

if (!function_exists('daraja')) {
    /**
     * Get the Daraja instance
     * 
     * @return \Codenson\Daraja\Daraja
     */
    function daraja()
    {
        return app('daraja');
    }
}

if (!function_exists('formatPhoneNumber')) {
    /**
     * Format phone number to 254XXXXXXXXX format
     * 
     * @param string $phoneNumber
     * @return string
     */
    function formatPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        } elseif (substr($phoneNumber, 0, 4) === '2540') {
            $phoneNumber = '254' . substr($phoneNumber, 4);
        } elseif (substr($phoneNumber, 0, 4) === '+254') {
            $phoneNumber = '254' . substr($phoneNumber, 4);
        }
        
        return $phoneNumber;
    }
}

if (!function_exists('formatMoney')) {
    /**
     * Format amount for M-PESA (integer without decimals)
     * 
     * @param float $amount
     * @return int
     */
    function formatMoney(float $amount): int
    {
        return (int) round($amount);
    }
}