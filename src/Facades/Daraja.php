<?php

namespace Codenson\Daraja\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Codenson\Daraja\Services\AuthService auth()
 * @method static string getAccessToken()
 * @method static \Codenson\Daraja\Services\STKPushService stkPush()
 * @method static \Codenson\Daraja\Services\C2BService c2b()
 * @method static \Codenson\Daraja\Services\B2CService b2c()
 * @method static \Codenson\Daraja\Services\TransactionStatusService transactionStatus()
 * @method static \Codenson\Daraja\Services\AccountBalanceService accountBalance()
 * @method static \Codenson\Daraja\Services\ReversalService reversal()
 * @method static \Codenson\Daraja\Services\TaxRemittanceService taxRemittance()
 * @method static \Codenson\Daraja\Services\BusinessPayBillService businessPayBill()
 * @method static \Codenson\Daraja\Services\BusinessBuyGoodsService businessBuyGoods()
 * @method static \Codenson\Daraja\Services\BillManagerService billManager()
 * @method static \Codenson\Daraja\Services\B2BExpressCheckoutService b2bExpressCheckout()
 * @method static \Codenson\Daraja\Services\PullTransactionsService pullTransactions()
 * @method static \Codenson\Daraja\Services\B2PochiService b2Pochi()
 * @method static \Codenson\Daraja\Services\IMSIService imsi()
 * @method static \Codenson\Daraja\Services\LipaNaBongaService lipaNaBonga()
 * @method static \Codenson\Daraja\Services\B2CAccountTopUpService b2cAccountTopUp()
 * @method static \Codenson\Daraja\Services\MpesaRatibaService mpesaRatiba()
 * @method static \Codenson\Daraja\Services\DynamicQRService dynamicQR()
 * 
 * @see \Codenson\Daraja\Daraja
 */
class Daraja extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'daraja';
    }
}