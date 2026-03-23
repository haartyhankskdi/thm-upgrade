<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Model\ServerRequestManagement;

class Server extends HealthCheck
{
    public function aroundSavePaymentInformationAndPlaceOrder(
        ServerRequestManagement $subject,
        callable $proceed,
        $cartId,
        $save_token,
        $token
    ) {
        if ($this->isValid()) {
            $result = $proceed($cartId, $save_token, $token);
        } else {
            $result = $subject->getResult();
            $result->setSuccess(false);
            $result->setErrorMessage(HealthCheck::ERROR_MESSAGE);
        }
        return $result;
    }
}
