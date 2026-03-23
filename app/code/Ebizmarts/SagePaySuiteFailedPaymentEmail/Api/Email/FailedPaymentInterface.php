<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email;

interface FailedPaymentInterface
{
    /**
     * @param $orderId
     * @param $statusDetail
     * @return \Magento\Sales\Api\PaymentFailuresInterface
     */
    public function sendEmail($orderId, $statusDetail);
}