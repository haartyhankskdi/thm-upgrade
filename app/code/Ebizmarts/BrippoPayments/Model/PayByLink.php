<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class PayByLink extends PaymentMethod
{
    const METHOD_CODE                               = 'brippo_payments_paybylink';
    const XML_PATH_ACTIVE                           = 'payment/brippo_payments_paybylink/active';
    const XML_PATH_STORE_CONFIG_CAPTURE_METHOD      = 'payment/brippo_payments_paybylink/capture_method';
    const XML_PATH_STORE_CONFIG_NOTE                = 'payment/brippo_payments_paybylink/note';
    const XML_PATH_STORE_CONFIG_HOSTED_CONFIRM_MSG  = 'payment/brippo_payments_paybylink/hosted_confirmation_message';
    const XML_PATH_STORE_CONFIG_SUCCESS_NOTE        = 'payment/brippo_payments_paybylink/checkout_success_note';
    const XML_PATH_STORE_CONFIG_EMAIL_SENDER        = 'payment/brippo_payments_paybylink/email_sender';
    const XML_PATH_STORE_CONFIG_EMAIL_TEMPLATE      = 'payment/brippo_payments_paybylink/email_template';

    const KEY_HOSTED_CONFIRMATION                   = 'hosted_confirmation';
    const DEFAULT_TEMPLATE_ID                   = 'payment_brippo_payments_brippo_payments_paybylink_email_template';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canRefundInvoicePartial = true;
}
