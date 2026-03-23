<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email\FailedPaymentInterface;

class TrySendPiEmail
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
     * @param EcommerceManagement $subject
     * @param string|null $errorMessage
     * @return void
     */
    public function afterTryToVoidTransactionAndUpdateResult(EcommerceManagement $subject, $errorMessage = null)
    {
        if ($this->shouldSendEmail($subject)) {
            /** @var int $quoteId */
            $quoteId =  $subject->getQuote()->getId();
            $errorMessage = $this->isSetErrorMessage($errorMessage)
                ? $errorMessage
                : $subject->getErrorMessage();
            $this->failedPayments->sendEmail($quoteId, $errorMessage);
        }
    }

    /**
     * @param string $errorMessage
     * @return bool
     */
    private function isSetErrorMessage($errorMessage)
    {
        return !empty($errorMessage);
    }

    /**
     * @param EcommerceManagement $subject
     * @return bool
     */
    private function shouldSendEmail($subject)
    {
        return $this->isActiveFailedPaymentEmail()
            && $subject->getPayResult() !== null
            && !$subject->isPaymentSuccessful();
    }

    /**
     * @return bool
     */
    private function isActiveFailedPaymentEmail()
    {
        return (bool)(int)$this->config->setMethodCode(Config::METHOD_PI)->getValue("failed_payment_email");
    }
}
