<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;

class Pi extends HealthCheck
{
    public function aroundPlaceOrder(EcommerceManagement $subject, callable $proceed)
    {
        if ($this->isValid()) {
            $result = $proceed();
        } else {
            $result = $subject->getResult();
            $result->setSuccess(false);
            $message = HealthCheck::ERROR_MESSAGE;
            $result->setErrorMessage(__($message));
        }
        return $result;
    }
}
