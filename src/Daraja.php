<?php

namespace Codenson\Daraja;

use Codenson\Daraja\Services\{
    AuthService,
    STKPushService,
    C2BService,
    B2CService,
    TransactionStatusService,
    AccountBalanceService,
    ReversalService,
    TaxRemittanceService,
    BusinessPayBillService,
    BusinessBuyGoodsService,
    BillManagerService,
    B2BExpressCheckoutService,
    PullTransactionsService,
    B2PochiService,
    IMSIService,
    LipaNaBongaService,
    B2CAccountTopUpService,
    MpesaRatibaService,
    DynamicQRService
};

class Daraja
{
    protected $config;
    protected $authService;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->authService = new AuthService($config);
    }

    /**
     * Get authentication service
     */
    public function auth(): AuthService
    {
        return $this->authService;
    }

    /**
     * Get access token
     */
    public function getAccessToken(): string
    {
        return $this->authService->getAccessToken();
    }

    /**
     * M-Pesa Express (STK Push)
     */
    public function stkPush(): STKPushService
    {
        return new STKPushService($this->config, $this->authService);
    }

    /**
     * Customer to Business (C2B)
     */
    public function c2b(): C2BService
    {
        return new C2BService($this->config, $this->authService);
    }

    /**
     * Business to Customer (B2C)
     */
    public function b2c(): B2CService
    {
        return new B2CService($this->config, $this->authService);
    }

    /**
     * Transaction Status
     */
    public function transactionStatus(): TransactionStatusService
    {
        return new TransactionStatusService($this->config, $this->authService);
    }

    /**
     * Account Balance
     */
    public function accountBalance(): AccountBalanceService
    {
        return new AccountBalanceService($this->config, $this->authService);
    }

    /**
     * Reversals
     */
    public function reversal(): ReversalService
    {
        return new ReversalService($this->config, $this->authService);
    }

    /**
     * Tax Remittance
     */
    public function taxRemittance(): TaxRemittanceService
    {
        return new TaxRemittanceService($this->config, $this->authService);
    }

    /**
     * Business PayBill
     */
    public function businessPayBill(): BusinessPayBillService
    {
        return new BusinessPayBillService($this->config, $this->authService);
    }

    /**
     * Business Buy Goods
     */
    public function businessBuyGoods(): BusinessBuyGoodsService
    {
        return new BusinessBuyGoodsService($this->config, $this->authService);
    }

    /**
     * Bill Manager
     */
    public function billManager(): BillManagerService
    {
        return new BillManagerService($this->config, $this->authService);
    }

    /**
     * B2B Express Checkout
     */
    public function b2bExpressCheckout(): B2BExpressCheckoutService
    {
        return new B2BExpressCheckoutService($this->config, $this->authService);
    }

    /**
     * Pull Transactions
     */
    public function pullTransactions(): PullTransactionsService
    {
        return new PullTransactionsService($this->config, $this->authService);
    }

    /**
     * Business to Pochi (B2Pochi)
     */
    public function b2Pochi(): B2PochiService
    {
        return new B2PochiService($this->config, $this->authService);
    }

    /**
     * IMSI Verification (SIM swap detection)
     */
    public function imsi(): IMSIService
    {
        return new IMSIService($this->config, $this->authService);
    }

    /**
     * Lipa na Bonga Points
     */
    public function lipaNaBonga(): LipaNaBongaService
    {
        return new LipaNaBongaService($this->config, $this->authService);
    }

    /**
     * B2C Account Top Up
     */
    public function b2cAccountTopUp(): B2CAccountTopUpService
    {
        return new B2CAccountTopUpService($this->config, $this->authService);
    }

    /**
     * M-Pesa Ratiba (Standing Orders)
     */
    public function mpesaRatiba(): MpesaRatibaService
    {
        return new MpesaRatibaService($this->config, $this->authService);
    }

    /**
     * Dynamic QR Code
     */
    public function dynamicQR(): DynamicQRService
    {
        return new DynamicQRService($this->config, $this->authService);
    }
}