<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Model\Email;

use Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email\FailedPaymentInterface;
use Magento\Sales\Api\PaymentFailuresInterface;

class FailedPayment implements FailedPaymentInterface
{
    /** @var PaymentFailuresInterface $paymentFailures */
    private $paymentFailures;

    public function __construct(
        PaymentFailuresInterface $paymentFailures
    ) {
        $this->paymentFailures     = $paymentFailures;
    }

    /**
     * @inerhitDoc
     */
    public function sendEmail($orderId, $statusDetail)
    {
        $this->paymentFailures->handle(
            (int)$orderId,
            $statusDetail
        );
    }
}
