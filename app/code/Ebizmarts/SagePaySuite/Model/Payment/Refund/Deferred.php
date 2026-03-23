<?php

namespace Ebizmarts\SagePaySuite\Model\Payment\Refund;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Payment\Model\InfoInterface;

class Deferred
{
    /**
     * @param InfoInterface $payment
     * @param array $associatedTransactions
     * @param string $vpsTxId
     * @return bool
     */
    public function shouldRefundOneTransaction($payment, $associatedTransactions, $vpsTxId)
    {
        return !$this->isDeferredPaymentAction($payment->getAdditionalInformation('paymentAction'))
            || empty($associatedTransactions)
            || !array_key_exists($vpsTxId, $associatedTransactions);
    }

    private function isDeferredPaymentAction($paymentAction)
    {
        return $paymentAction === Config::ACTION_DEFER
            || $paymentAction === Config::ACTION_DEFER_PI
            || $paymentAction === Config::ACTION_REPEAT_DEFERRED;
    }
}
