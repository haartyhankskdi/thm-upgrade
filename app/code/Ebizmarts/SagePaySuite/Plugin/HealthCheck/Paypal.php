<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Model\PayPalRequestManagement;

class Paypal extends HealthCheck
{
    public function aroundSavePaymentInformationAndPlaceOrder(
        PayPalRequestManagement $subject,
        callable $proceed,
        $cartId,
        $requestData
    ) {
        if ($this->isValid()) {
            $result = $proceed($cartId, $requestData);
        } else {
            $result = $subject->getResult();
            $result->setSuccess(false);
            $result->setErrorMessage(HealthCheck::ERROR_MESSAGE);
        }
        return $result;
    }
}
