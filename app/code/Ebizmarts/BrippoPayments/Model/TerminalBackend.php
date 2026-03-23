<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class TerminalBackend extends PaymentMethod
{
    const METHOD_CODE                   = 'brippo_payments_terminal_moto_backend';
    const XML_PATH_ACTIVE               = 'payment/brippo_payments_terminal_moto_backend/active';
    const XML_PATH_CAPTURE_METHOD       = 'payment/brippo_payments_terminal_moto_backend/capture_method';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = false;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded      = true;
}
