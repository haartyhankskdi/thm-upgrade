<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email\FailedPaymentInterface;

class TrySendPiEmailOnCallback
{
    /** @var FailedPaymentInterface  */
    private $failedPayments;

    /** @var Config */
    private $config;

    public function __construct(
        FailedPaymentInterface $failedPayments,
        Config $config
    ) {
        $this->failedPayments    = $failedPayments;
        $this->config = $config;
    }

    /**
     * @param ThreeDSecureCallbackManagement $subject
     */
    public function afterPlaceOrder(ThreeDSecureCallbackManagement $subject, $result)
    {
        if (!$this->isActiveFailedPaymentEmail()) {
            return $result;
        }
        $paymentStatus = $result->getErrorMessage();
        if (!empty($paymentStatus)) {
            $quoteId =  $subject->getQuoteIdFromParams();
            $this->failedPayments->sendEmail($quoteId, $paymentStatus);
        }
        return $result;
    }

    /**
     * @return bool
     */
    private function isActiveFailedPaymentEmail()
    {
        return (bool)(int)$this->config->setMethodCode(Config::METHOD_PI)->getValue("failed_payment_email");
    }
}
