<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class PayByLinkMoto extends PaymentMethod
{
    const METHOD_CODE                        = 'brippo_payments_paybylink_backend';
    const XML_PATH_ACTIVE                    = 'payment/brippo_payments_paybylink_backend/active';
    const XML_PATH_CONFIG_HOSTED_CONFIRM_MSG = 'payment/brippo_payments_paybylink_backend/hosted_confirmation_message';
    const XML_PATH_CONFIG_CAPTURE_METHOD     = 'payment/brippo_payments_paybylink_backend/capture_method';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = false;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded      = true;
}
