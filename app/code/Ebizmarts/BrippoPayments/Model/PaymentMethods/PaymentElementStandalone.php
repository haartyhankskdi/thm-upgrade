<?php

namespace Ebizmarts\BrippoPayments\Model\PaymentMethods;

class PaymentElementStandalone extends PaymentMethod
{
    const METHOD_CODE                           = 'brippo_payments_paymentelement_standalone';
    const XML_PATH_ACTIVE                       = 'payment/brippo_payments_paymentelement_standalone/active';
    const XML_PATH_CAPTURE_METHOD               = 'payment/brippo_payments_paymentelement_standalone/capture_method';
    const XML_PATH_THEME                        = 'payment/brippo_payments_paymentelement_standalone/theme';
    const CONFIG_PAYMENT_METHOD                 = 'payment/brippo_payments_paymentelement_standalone/payment_method';
    const CONFIG_TITLE                          = 'payment/brippo_payments_paymentelement_standalone/title';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canRefundInvoicePartial = true;
}
