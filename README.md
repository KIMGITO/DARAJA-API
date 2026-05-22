# Laravel Daraja Package

A comprehensive Laravel package for Safaricom Daraja APIs - Complete M-PESA integration made easy.

## 📋 Table of Contents
- [Features](#Features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage Examples](#usage-examples)
  - [Authentication](#authentication)
  - [M-Pesa Express (STK Push)](#m-pesa-express-stk-push)
  - [Customer to Business (C2B)](#customer-to-business-c2b)
  - [Business to Customer (B2C)](#business-to-customer-b2c)
  - [Transaction Status](#transaction-status)
  - [Account Balance](#account-balance)
  - [Transaction Reversal](#transaction-reversal)
  - [Tax Remittance](#tax-remittance)
  - [Business PayBill](#business-paybill)
  - [Business Buy Goods](#business-buy-goods)
  - [Bill Manager](#bill-manager)
  - [B2B Express Checkout](#b2b-express-checkout)
  - [Pull Transactions](#pull-transactions)
  - [Business to Pochi (B2Pochi)](#business-to-pochi-b2pochi)
  - [IMSI Verification](#imsi-verification-sim-swap-detection)
  - [Lipa na Bonga Points](#lipa-na-bonga-points)
  - [B2C Account Top Up](#b2c-account-top-up)
  - [M-Pesa Ratiba (Standing Orders)](#m-pesa-ratiba-standing-orders)
  - [Dynamic QR Code](#dynamic-qr-code)
- [Webhook Handling](#webhook-handling)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [License](#license)

## ✨ Features

- ✅ OAuth Authentication with automatic token caching
- ✅ M-Pesa Express (STK Push) with query capability
- ✅ Customer to Business (C2B) with URL registration
- ✅ Business to Customer (B2C) payments
- ✅ Transaction Status Query
- ✅ Account Balance Enquiry
- ✅ Reversal API
- ✅ Tax Remittance to KRA
- ✅ Business PayBill
- ✅ Business Buy Goods
- ✅ Bill Manager (create/update bills)
- ✅ B2B Express Checkout (USSD Push to till)
- ✅ Pull Transactions (reconciliation)
- ✅ Business to Pochi (Micro SME wallet)
- ✅ IMSI Verification (SIM Swap Detection)
- ✅ Lipa na Bonga Points
- ✅ B2C Account Top Up
- ✅ M-Pesa Ratiba (Standing Orders)
- ✅ Dynamic QR Code Generation
- ✅ Sandbox & Production support
- ✅ Database migration for transaction logging

## 📦 Requirements

- PHP 8.0 or higher
- Laravel 9.x,  or higher
- Guzzle HTTP Client

## 🔧 Installation

```bash
composer require codenson/laravel-daraja

php artisan vendor:publish --tag=daraja-config

php artisan vendor:publish --tag=daraja-migrations
php artisan migrate
```
## ⚙️ Configuration
```bash
# API Environment (sandbox or production)
MPESA_ENVIRONMENT=sandbox

# API Credentials
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret

# Business Details
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey
MPESA_TILL_NUMBER=your_till_number
MPESA_PAYBILL=your_paybill_number

# Initiator Credentials
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_SECURITY_CREDENTIAL=your_security_credential

# Callback URLs
MPESA_STK_CALLBACK_URL=https://your-domain.com/api/mpesa/stk-callback
MPESA_C2B_CONFIRMATION_URL=https://your-domain.com/api/mpesa/c2b-confirmation
MPESA_C2B_VALIDATION_URL=https://your-domain.com/api/mpesa/c2b-validation
MPESA_B2C_TIMEOUT_URL=https://your-domain.com/api/mpesa/b2c-timeout
MPESA_B2C_RESULT_URL=https://your-domain.com/api/mpesa/b2c-result

# Optional
MPESA_LOGGING=true
MPESA_TIMEOUT=30

```

## 🚀 Usage Examples
```php
<?php

namespace App\Http\Controllers;

use Codenson\Daraja\Facades\Daraja;
use Codenson\Daraja\Exceptions\DarajaException;

class MpesaController extends Controller
{
  ```
```php
    //Get access token (auto-managed, but you can manually get it)
  public function getToken()
  {
      try {
          $token = Daraja::getAccessToken();
          return response()->json(['token' => $token]);
      } catch (DarajaException $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }
```
  
```php
  // Initiate STK Push
  public function stkPush(Request $request)
{
      try 
      {
          $response = Daraja::stkPush()->request([
              'amount' => 10,                              // Amount
              'phone_number' => '254712345678',            // Customer phone
              'account_reference' => 'INV-001',            // Order number
              'transaction_desc' => 'Payment for goods',   // Description
              'transaction_type' => 'CustomerPayBillOnline', // Optional
          ]);
          
          if ($response['ResponseCode'] === '0') 
          {
              return response()->json([
                  'success' => true,
                  'checkout_request_id' => $response['CheckoutRequestID']
              ]);
          }
      } catch (DarajaException $e) 
      {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }
```
```php
  // Query STK Push status
  public function queryStatus($checkoutRequestID)
  {
      try 
      {
          $response = Daraja::stkPush()->query($checkoutRequestID);
          
          if ($response['ResultCode'] === '0') {
              return response()->json([
                  'success' => true,
                  'receipt_number' => $response['CallbackMetadata']['Item'][1]['Value']
              ]);
          }
      } catch (DarajaException $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }

```
```php
  // Register C2B URLs
public function registerC2B()
{
    try {
        $response = Daraja::c2b()->registerURLs(
            'https://your-domain.com/c2b/confirmation',
            'https://your-domain.com/c2b/validation'
        );
        
        return response()->json($response);
    } catch (DarajaException $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

```
```php
  // Simulate C2B (sandbox only)
  public function simulateC2B(Request $request)
  {
      try 
      {
          $response = Daraja::c2b()->simulate(
              '254712345678',  // Phone number
              100,             // Amount
              'CustomerPayBillOnline' // Command ID
          );
          
          return response()->json($response);
      } catch (DarajaException $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }
```
```php
    // Send B2C payment
    public function sendB2C(Request $request)
    {
        try 
        {
            $response = Daraja::b2c()->send([
                'amount' => 1000,                        // Amount
                'phone_number' => '254712345678',        // Recipient phone
                'command_id' => 'BusinessPayment',       // SalaryPayment, BusinessPayment, PromotionPayment
                'remarks' => 'Salary payment',           // Remarks
                'occasion' => 'January salary',          // Optional occasion
            ]);
            
            if ($response['ResponseCode'] === '0') {
                return response()->json([
                    'success' => true,
                    'conversation_id' => $response['ConversationID']
                ]);
            }
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Query account balance
    public function checkBalance()
    {
        try 
        {
            $response = Daraja::accountBalance()->query([
                'shortcode' => '174379',                 // Optional, uses config by default
                'identifier_type' => 4,                  // 1=MSISDN,2=Till,3=Shortcode,4=Organization
                'remarks' => 'Daily balance check',      // Remarks
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Reverse a transaction
    public function reverseTransaction(Request $request)
    {
        try 
        {
            $response = Daraja::reversal()->reverse([
                'transaction_id' => 'SDF45T56789',      // Transaction to reverse
                'amount' => 1000,                        // Amount to reverse
                'receiver_shortcode' => '174379',        // Optional
                'remarks' => 'Customer requested reversal',
                'occasion' => 'Wrong amount',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Remit tax to KRA
    public function remitTax(Request $request)
    {
        try 
        {
            $response = Daraja::taxRemittance()->remit([
                'amount' => 5000,                        // Tax amount
                'payer_number' => '254712345678',        // Payer phone
                'pin' => 'A001234567',                   // KRA PIN
                'period' => '2025-01',                   // Tax period (Y-m)
                'remarks' => 'Monthly tax remittance',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Pay bill from business account
    public function payBill(Request $request)
    {
        try 
        {
            $response = Daraja::businessPayBill()->pay([
                'amount' => 15000,                       // Amount
                'paybill_number' => '123456',            // Paybill to pay
                'account_reference' => 'INV-12345',      // Account reference
                'remarks' => 'Invoice payment',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Pay for goods from business account
    public function buyGoods(Request $request)
    {
        try 
        {
            $response = Daraja::businessBuyGoods()->pay([
                'amount' => 5000,                        // Amount
                'till_number' => '123456',               // Till number
                'account_reference' => 'ORDER-001',      // Order reference
                'remarks' => 'Goods payment',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Create a bill
    public function createBill(Request $request)
    {
        try 
        {
            $response = Daraja::billManager()->createBill([
                'customer_name' => 'John Doe',           // Customer name
                'customer_phone' => '254712345678',      // Customer phone
                'amount' => 2500,                        // Bill amount
                'due_date' => '2025-02-15',              // Due date (Y-m-d)
                'bill_reference' => 'BILL-001',          // Unique reference
                'description' => 'Water bill payment',   // Description
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Update bill status
    public function updateBill($billReference)
    {
        try 
        {
            $response = Daraja::billManager()->updateBill(
                $billReference,
                'PAID'  // PAID, PENDING, CANCELLED
            );
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Initiate B2B Express Checkout (USSD Push)
    public function b2bExpressCheckout(Request $request)
    {
        try 
        {
            $response = Daraja::b2bExpressCheckout()->push([
                'amount' => 10000,                       // Amount
                'payer_till' => '123456',                // Payer till number
                'payee_till' => '654321',                // Payee till number
                'account_reference' => 'TRANS-001',      // Reference
                'remarks' => 'B2B payment',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Pull transactions for reconciliation
    public function pullTransactions(Request $request)
    {
        try 
        {
            $response = Daraja::pullTransactions()->query([
                'start_date' => '2025-01-01',            // Start date (Y-m-d)
                'end_date' => '2025-01-31',              // End date (Y-m-d)
                'transaction_type' => 'C2B',             // C2B, B2C, etc.
                'page' => 1,                             // Page number
                'limit' => 100,                          // Results per page
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Send payment to Pochi La Biashara
    public function sendToPochi(Request $request)
    {
        try 
        {
            $response = Daraja::b2Pochi()->send([
                'amount' => 5000,                        // Amount
                'phone_number' => '254712345678',        // Recipient phone
                'pochi_number' => 'POCHI123456',         // Pochi account number
                'remarks' => 'Payment to Micro SME',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Query IMSI information
    public function verifyIMSI(Request $request)
    {
        try 
        {
            $response = Daraja::imsi()->query([
                'phone_number' => '254712345678',        // Phone to verify
                'include_imsi' => true,                  // Include IMSI
                'include_sim_swap' => true,              // Include SIM swap info
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Check SIM swap specifically
    public function checkSimSwap($phoneNumber)
    {
        try 
        {
            $response = Daraja::imsi()->checkSimSwap($phoneNumber);
            
            if ($response['sim_swap_detected']) {
                // Take action - flag account, request additional verification
                return response()->json(['warning' => 'SIM card recently changed']);
            }
            
            return response()->json(['safe' => true]);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Pay with Bonga Points
    public function payWithBonga(Request $request)
    {
        try 
        {
            $response = Daraja::lipaNaBonga()->pay([
                'amount' => 500,                         // Cash amount
                'phone_number' => '254712345678',        // Customer phone
                'bonga_points' => 1000,                  // Bonga points to use
                'till_number' => '123456',               // Till number
                'account_reference' => 'ORDER-001',      // Reference
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Check Bonga Points balance
    public function checkBongaBalance($phoneNumber)
    {
        try 
        {
            $response = Daraja::lipaNaBonga()->checkBalance($phoneNumber);
            
            return response()->json([
                'bonga_points' => $response['balance']
            ]);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Load funds to B2C shortcode
    public function topUpB2CAccount(Request $request)
    {
        try 
        {
            $response = Daraja::b2cAccountTopUp()->topUp([
                'amount' => 50000,                       // Amount to load
                'reference' => 'TOPUP-001',              // Reference
                'remarks' => 'Monthly fund loading',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Create standing order
    public function createStandingOrder(Request $request)
    {
        try 
        {
            $response = Daraja::mpesaRatiba()->create([
                'amount' => 1000,                        // Amount per transaction
                'phone_number' => '254712345678',        // Customer phone
                'start_date' => '2025-02-01',            // Start date (Y-m-d)
                'end_date' => '2025-12-31',              // End date (Y-m-d)
                'frequency' => 'MONTHLY',                // DAILY, WEEKLY, MONTHLY
                'day_of_month' => 15,                    // For monthly (1-31)
                'day_of_week' => 'MON',                  // For weekly (MON, TUE, etc.)
                'account_reference' => 'LOAN-001',       // Reference
                'remarks' => 'Loan repayment',
            ]);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Cancel standing order
    public function cancelStandingOrder($orderId)
    {
        try 
        {
            $response = Daraja::mpesaRatiba()->cancel($orderId);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Query standing order status
    public function queryStandingOrder($orderId)
    {
        try 
        {
            $response = Daraja::mpesaRatiba()->query($orderId);
            
            return response()->json($response);
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
```
```php
    // Generate dynamic QR code
    public function generateQRCode(Request $request)
    {
        try 
        {
            $response = Daraja::dynamicQR()->generate([
                'merchant_name' => 'My Store',           // Merchant name
                'ref_no' => 'INV-001',                   // Reference number
                'amount' => 1500,                        // Amount
                'trx_code' => 'BG',                      // BG=Buy Goods, PB=Paybill
                'cpi' => '123456',                       // Till/Paybill number
                'size' => '300',                         // QR size in pixels
            ]);
            
            if ($response['ResponseCode'] === '0') {
                return response()->json([
                    'qr_code' => $response['QRCode'],    // Base64 encoded image
                    'response_description' => $response['ResponseDescription']
                ]);
            }
        } catch (DarajaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## 📡 Webhook Handling
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codenson\Daraja\Models\DarajaTransaction;

class MpesaCallbackController extends Controller
{
  ```
```php
    // STK Push Callback
    public function stkCallback(Request $request)
    {
        $data = $request->all();
        $stkCallback = $data['Body']['stkCallback'];
        
        // Store transaction
        DarajaTransaction::create([
            'transaction_type' => 'STK',
            'merchant_request_id' => $stkCallback['MerchantRequestID'],
            'checkout_request_id' => $stkCallback['CheckoutRequestID'],
            'result_code' => $stkCallback['ResultCode'],
            'result_desc' => $stkCallback['ResultDesc'],
            'amount' => $stkCallback['CallbackMetadata']['Item'][0]['Value'] ?? null,
            'mpesa_receipt_number' => $stkCallback['CallbackMetadata']['Item'][1]['Value'] ?? null,
            'phone_number' => $stkCallback['CallbackMetadata']['Item'][4]['Value'] ?? null,
            'raw_response' => json_encode($data)
        ]);
        
        if ($stkCallback['ResultCode'] === 0) {
            // Payment successful - update order, send notification, etc.
            $this->handleSuccessfulPayment($stkCallback);
        }
        
        // Always return success to M-PESA
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
    
```
```php

    // C2B Confirmation
    public function c2bConfirmation(Request $request)
    {
        $data = $request->all();
        
        DarajaTransaction::create([
            'transaction_type' => 'C2B',
            'transaction_id' => $data['TransID'],
            'amount' => $data['TransAmount'],
            'phone_number' => $data['MSISDN'],
            'account_reference' => $data['BillRefNumber'],
            'mpesa_receipt_number' => $data['TransID'],
            'raw_response' => json_encode($data)
        ]);
        
        // Process the payment
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
```
```php
    // C2B Validation
    public function c2bValidation(Request $request)
    {
        $data = $request->all();
        
        // Validate transaction (check account, limits, etc.)
        if ($this->isValidTransaction($data)) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }
        
        return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Validation failed']);
    }
```
```php   
    // B2C Result
    public function b2cResult(Request $request)
    {
        $data = $request->all();
        
        DarajaTransaction::create([
            'transaction_type' => 'B2C',
            'result_code' => $data['Result']['ResultCode'],
            'result_desc' => $data['Result']['ResultDesc'],
            'amount' => $data['Result']['ResultParameters']['ResultParameter'][0]['Value'] ?? null,
            'phone_number' => $data['Result']['ResultParameters']['ResultParameter'][1]['Value'] ?? null,
            'raw_response' => json_encode($data)
        ]);
        
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
```
```php
    private function handleSuccessfulPayment($callback)
    {
        // Update order status
        // Send email/SMS notification
        // Generate receipt
        // Trigger other business logic
    }
}
```

##  📡 Webhook Routes
```php
Route::prefix('mpesa')->group(function () {
    Route::post('/stk-callback', [MpesaCallbackController::class, 'stkCallback']);
    Route::post('/c2b-confirmation', [MpesaCallbackController::class, 'c2bConfirmation']);
    Route::post('/c2b-validation', [MpesaCallbackController::class, 'c2bValidation']);
    Route::post('/b2c-result', [MpesaCallbackController::class, 'b2cResult']);
    Route::post('/b2c-timeout', [MpesaCallbackController::class, 'b2cTimeout']);
});
```

```bash
# =============================================
# DARAJA API CONFIGURATION
# =============================================

# API Environment (sandbox or production)
MPESA_ENVIRONMENT=sandbox

# API Credentials (get from Safaricom Developer Portal)
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here

# Business Details
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey_here
MPESA_TILL_NUMBER=your_till_number_here
MPESA_PAYBILL=your_paybill_number_here

# Initiator Credentials (for B2C, Reversals, Account Balance, Transaction Status)
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_SECURITY_CREDENTIAL=your_security_credential_here

# =============================================
# CALLBACK URLs
# =============================================

# STK Push Callbacks
MPESA_STK_CALLBACK_URL=https://your-domain.com/api/mpesa/stk-callback

# C2B Callbacks
MPESA_C2B_CONFIRMATION_URL=https://your-domain.com/api/mpesa/c2b-confirmation
MPESA_C2B_VALIDATION_URL=https://your-domain.com/api/mpesa/c2b-validation

# B2C Callbacks
MPESA_B2C_TIMEOUT_URL=https://your-domain.com/api/mpesa/b2c-timeout
MPESA_B2C_RESULT_URL=https://your-domain.com/api/mpesa/b2c-result

# Transaction Status Callback
MPESA_TRANSACTION_STATUS_URL=https://your-domain.com/api/mpesa/transaction-status

# Account Balance Callback
MPESA_ACCOUNT_BALANCE_URL=https://your-domain.com/api/mpesa/account-balance

# Reversal Callback
MPESA_REVERSAL_URL=https://your-domain.com/api/mpesa/reversal

# =============================================
# OPTIONAL SETTINGS
# =============================================

# Enable/disable logging of API requests and responses
MPESA_LOGGING=true

# HTTP request timeout in seconds
MPESA_TIMEOUT=30

# Log channel for Daraja logs (daily, single, slack, etc.)
MPESA_LOG_CHANNEL=daily

# Result type (array, object, raw)
MPESA_RESULT_TYPE=array
```

```bash
# =============================================
# SANDBOX CREDENTIALS (for local development and testing)
# =============================================
MPESA_ENVIRONMENT=sandbox
MPESA_CONSUMER_KEY=YOUR_SANDBOX_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_SANDBOX_CONSUMER_SECRET
MPESA_SHORTCODE=174379
MPESA_PASSKEY=sandbox_passkey
MPESA_INITIATOR_NAME=sandbox_initiator
MPESA_INITIATOR_PASSWORD=sandbox_password
MPESA_SECURITY_CREDENTIAL=sandbox_credential
MPESA_STK_CALLBACK_URL=https://your-ngrok-url.ngrok.io/api/mpesa/stk-callback
```


```bash
# =============================================
# PRODUCTION CREDENTIALS (use real credentials when deploying)
# =============================================
MPESA_ENVIRONMENT=production
MPESA_CONSUMER_KEY=LIVE_CONSUMER_KEY
MPESA_CONSUMER_SECRET=LIVE_CONSUMER_SECRET
MPESA_SHORTCODE=123456  # Your live shortcode
MPESA_PASSKEY=live_passkey
MPESA_INITIATOR_NAME=live_initiator
MPESA_INITIATOR_PASSWORD=live_password
MPESA_SECURITY_CREDENTIAL=live_credential
MPESA_STK_CALLBACK_URL=https://your-domain.com/api/mpesa/stk-callback
```


## 🧪 Testing
``````php
# Run all tests
composer test

# Run specific test
./vendor/bin/phpunit tests/Unit/STKPushServiceTest.php

# Run with coverage
composer test-coverage

# Test API configuration (returns current config values)
Route::get('/mpesa/test-config', function () {
    return [
        'environment' => config('daraja.environment'),
        'shortcode' => config('daraja.shortcode'),
        'has_consumer_key' => !empty(config('daraja.consumer_key')),
        'has_consumer_secret' => !empty(config('daraja.consumer_secret')),
        'has_passkey' => !empty(config('daraja.passkey')),
        'has_callback_urls' => !empty(config('daraja.callback_urls.stk_push')),
    ];
});
``````


## 🔐 Security Best Practices
```bash
    Always validate callbacks - Ensure they come from Safaricom IPs

    Store transaction IDs - For reconciliation and querying

    Use HTTPS - All callbacks must be HTTPS endpoints

    Implement idempotency - Prevent duplicate transaction processing

    Log everything - Keep audit trails for all API calls

    Secure credentials - Never commit .env files to version control
    Handle errors gracefully - Implement retry logic for transient failures
```

## 📚 Additional Resources

  - [Daraja API Documentation](https://developer.safaricom.co.ke/docs)
  - [Safaricom Developer Portal](https://developer.safaricom.co.ke)
  - [M-PESA Documentation](https://developer.safaricom.co.ke/docs)
  - [Laravel Documentation](https://laravel.com/docs)
