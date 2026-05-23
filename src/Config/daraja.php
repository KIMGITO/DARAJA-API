<?php

return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'shortcode' => env('MPESA_SHORTCODE', ''),
    'passkey' => env('MPESA_PASSKEY', ''),
    'initiator' => env('MPESA_INITIATOR_NAME', ''),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL', ''),
    'callback_urls' => [
        'stk_push' => env('MPESA_STK_CALLBACK_URL', null),
        'c2b_confirmation' => env('MPESA_C2B_CONFIRMATION_URL', null),
        'c2b_validation' => env('MPESA_C2B_VALIDATION_URL', null),
        'b2c_timeout' => env('MPESA_B2C_TIMEOUT_URL', null),
        'b2c_result' => env('MPESA_B2C_RESULT_URL', null),
    ],
    'endpoints' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'account_balance' => 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
            'reversal' => 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'b2c' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'account_balance' => 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query',
            'reversal' => 'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
        ],
    ],
    'result_type' => 'array',
    'timeout' => 30,
];