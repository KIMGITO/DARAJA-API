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

/**
 * Daraja M-PESA API Main Class
 */
class Daraja
{
    protected array $config;
    protected AuthService $authService;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->authService = new AuthService($config);
    }

    public function auth(): AuthService
    {
        return $this->authService;
    }

    public function getAccessToken(): string
    {
        return $this->authService->getAccessToken();
    }

    public function stkPush(): STKPushService
    {
        return new STKPushService($this->config, $this->authService);
    }

    public function c2b(): C2BService
    {
        return new C2BService($this->config, $this->authService);
    }

    public function b2c(): B2CService
    {
        return new B2CService($this->config, $this->authService);
    }

    public function transactionStatus(): TransactionStatusService
    {
        return new TransactionStatusService($this->config, $this->authService);
    }

    public function accountBalance(): AccountBalanceService
    {
        return new AccountBalanceService($this->config, $this->authService);
    }

    public function reversal(): ReversalService
    {
        return new ReversalService($this->config, $this->authService);
    }

    public function taxRemittance(): TaxRemittanceService
    {
        return new TaxRemittanceService($this->config, $this->authService);
    }

    public function businessPayBill(): BusinessPayBillService
    {
        return new BusinessPayBillService($this->config, $this->authService);
    }

    public function businessBuyGoods(): BusinessBuyGoodsService
    {
        return new BusinessBuyGoodsService($this->config, $this->authService);
    }

    public function billManager(): BillManagerService
    {
        return new BillManagerService($this->config, $this->authService);
    }

    public function b2bExpressCheckout(): B2BExpressCheckoutService
    {
        return new B2BExpressCheckoutService($this->config, $this->authService);
    }

    public function pullTransactions(): PullTransactionsService
    {
        return new PullTransactionsService($this->config, $this->authService);
    }

    public function b2Pochi(): B2PochiService
    {
        return new B2PochiService($this->config, $this->authService);
    }

    public function imsi(): IMSIService
    {
        return new IMSIService($this->config, $this->authService);
    }

    public function lipaNaBonga(): LipaNaBongaService
    {
        return new LipaNaBongaService($this->config, $this->authService);
    }

    public function b2cAccountTopUp(): B2CAccountTopUpService
    {
        return new B2CAccountTopUpService($this->config, $this->authService);
    }

    public function mpesaRatiba(): MpesaRatibaService
    {
        return new MpesaRatibaService($this->config, $this->authService);
    }

    public function dynamicQR(): DynamicQRService
    {
        return new DynamicQRService($this->config, $this->authService);
    }
}