<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface ScaTransTypeInterface
{
    /**
     * Values derived from the 8583 ISO Standard.
     */

    public const GOOD_SERVICE_PURCHASE = "GoodsAndServicePurchase";
    public const CHECK_ACCEPTANCE = "CheckAcceptance";
    public const ACCOUNT_FUNDING = "AccountFunding";
    public const QUASI_CASH_TRANSACTION = "QuasiCashTransaction";
    public const PREPAID_ACTIVATION_AND_LOAD = "PrepaidActivationAndLoad";
}
