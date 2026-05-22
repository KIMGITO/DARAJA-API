<?php

if (!function_exists('daraja')) {
    function daraja()
    {
        return app('daraja');
    }
}

if (!function_exists('formatPhoneNumber')) {
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
    function formatMoney(float $amount): int
    {
        return (int) round($amount);
    }
}