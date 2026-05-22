<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Daraja API Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing or 'production' for live environment
    |
    */
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Consumer Key
    |--------------------------------------------------------------------------
    |
    | Your Daraja app consumer key from Safaricom
    |
    */
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Consumer Secret
    |--------------------------------------------------------------------------
    |
    | Your Daraja app consumer secret from Safaricom
    |
    */
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Shortcode/Paybill/Till Number
    |--------------------------------------------------------------------------
    |
    | Your M-PESA shortcode, paybill or till number
    |
    */
    'shortcode' => env('MPESA_SHORTCODE', ''),
    
    'paybill' => env('MPESA_PAYBILL', ''),
    'till_number' => env('MPESA_TILL_NUMBER', ''),

    /*
    |--------------------------------------------------------------------------
    | Passkey
    |--------------------------------------------------------------------------
    |
    | Your M-PESA passkey for STK Push
    |
    */
    'passkey' => env('MPESA_PASSKEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs where M-PESA will send transaction results
    |
    */
    'callback_urls' => [
        'stk_push' => env('MPESA_STK_CALLBACK_URL', null),
        'c2b_confirmation' => env('MPESA_C2B_CONFIRMATION_URL', null),
        'c2b_validation' => env('MPESA_C2B_VALIDATION_URL', null),
        'b2c_timeout' => env('MPESA_B2C_TIMEOUT_URL', null),
        'b2c_result' => env('MPESA_B2C_RESULT_URL', null),
        'transaction_status' => env('MPESA_TRANSACTION_STATUS_URL', null),
        'account_balance' => env('MPESA_ACCOUNT_BALANCE_URL', null),
        'reversal' => env('MPESA_REVERSAL_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | Daraja API endpoints (don't modify unless updated by Safaricom)
    |
    */
    'endpoints' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'c2b_simulate' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate',
            'b2c' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
            'account_balance' => 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
            'reversal' => 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request',
            'tax_remittance' => 'https://apisandbox.safaricom.et/mpesa/taxremittance/v1/remit',
            'business_paybill' => 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            'business_buygoods' => 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            'bill_manager' => 'https://sandbox.safaricom.co.ke/mpesa/billmanager/v1/create',
            'b2b_express_checkout' => 'https://sandbox.safaricom.co.ke/mpesa/b2bexpress/v1/push',
            'pull_transactions' => 'https://sandbox.safaricom.co.ke/mpesa/pulltransactions/v1/query',
            'b2_pochi' => 'https://sandbox.safaricom.co.ke/mpesa/b2cpochi/v1/paymentrequest',
            'imsi' => 'https://sandbox.safaricom.co.ke/imsi/v1/query',
            'lipa_na_bonga' => 'https://sandbox.safaricom.co.ke/mpesa/lipanabonga/v1/pay',
            'b2c_account_topup' => 'https://sandbox.safaricom.co.ke/mpesa/b2caccounttopup/v1/paymentrequest',
            'mpesa_ratiba' => 'https://sandbox.safaricom.co.ke/mpesa/standingorder/v1/create',
            'dynamic_qr' => 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'c2b_register' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'c2b_simulate' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate',
            'b2c' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query',
            'account_balance' => 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query',
            'reversal' => 'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
            'tax_remittance' => 'https://api.safaricom.et/mpesa/taxremittance/v1/remit',
            'business_paybill' => 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            'business_buygoods' => 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            'bill_manager' => 'https://api.safaricom.co.ke/mpesa/billmanager/v1/create',
            'b2b_express_checkout' => 'https://api.safaricom.co.ke/mpesa/b2bexpress/v1/push',
            'pull_transactions' => 'https://api.safaricom.co.ke/mpesa/pulltransactions/v1/query',
            'b2_pochi' => 'https://api.safaricom.co.ke/mpesa/b2cpochi/v1/paymentrequest',
            'imsi' => 'https://api.safaricom.co.ke/imsi/v1/query',
            'lipa_na_bonga' => 'https://api.safaricom.co.ke/mpesa/lipanabonga/v1/pay',
            'b2c_account_topup' => 'https://api.safaricom.co.ke/mpesa/b2caccounttopup/v1/paymentrequest',
            'mpesa_ratiba' => 'https://api.safaricom.co.ke/mpesa/standingorder/v1/create',
            'dynamic_qr' => 'https://api.safaricom.co.ke/mpesa/qrcode/v1/generate',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Result Type
    |--------------------------------------------------------------------------
    |
    | Whether to return raw response or processed array
    |
    */
    'result_type' => 'array', // 'array', 'object', 'raw'

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable logging of API requests and responses
    |
    */
    'logging' => [
        'enabled' => env('MPESA_LOGGING', true),
        'channel' => env('MPESA_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout in seconds
    |
    */
    'timeout' => env('MPESA_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Initiator Credentials (for B2C, Reversals, etc.)
    |--------------------------------------------------------------------------
    */
    'initiator' => env('MPESA_INITIATOR_NAME', ''),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD', ''),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL', ''),
];