<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Model\FormRequestManagement;

class Form extends HealthCheck
{
    public function aroundGetEncryptedRequest(FormRequestManagement $subject, callable $proceed, $cartId)
    {
        if ($this->isValid()) {
            $result = $proceed($cartId);
        } else {
            $result = $subject->getResult();
            $result->setSuccess(false);
            $result->setErrorMessage(HealthCheck::ERROR_MESSAGE);
        }
        return $result;
    }
}
