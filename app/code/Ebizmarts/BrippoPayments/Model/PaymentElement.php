<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class PaymentElement extends PaymentMethod
{
    const METHOD_CODE                   = 'brippo_payments_paymentelement';
    const THEME_CODE_STRIPE             = 'stripe';
    const THEME_CODE_NIGHT              = 'night';
    const THEME_CODE_FLAT               = 'flat';
    const LAYOUT_CODE_TABS              = 'tabs';
    const LAYOUT_CODE_ACCORDION         = 'accordion';
    const LABELS_CODE_FLOATING          = 'floating';

    const STORE_LOCATION_RETRY_MODAL    = 'failsafe_popup';

    const XML_PATH_ACTIVE                       = 'payment/brippo_payments_paymentelement/active';
    const XML_PATH_CAPTURE_METHOD               = 'payment/brippo_payments_paymentelement/capture_method';
    const XML_PATH_THREE_D_SECURE               = 'payment/brippo_payments_paymentelement/three_d_secure';
    const XML_PATH_THREE_D_SECURE_THRESHOLD     = 'payment/brippo_payments_paymentelement/three_d_secure_threshold';
    const XML_PATH_STATUS_TRIGGERING_CAPTURE    = 'payment/brippo_payments_paymentelement/status_triggering_capture';
    const XML_PATH_THEME                        = 'payment/brippo_payments_paymentelement/theme';
    const XML_PATH_LAYOUT                       = 'payment/brippo_payments_paymentelement/layout';
    const XML_PATH_LABELS                       = 'payment/brippo_payments_paymentelement/labels';
    const XML_PATH_INCLUDE_WALLETS              = 'payment/brippo_payments_paymentelement/include_wallets';
    const XML_PATH_DISPLAY_PM_LOGOS             = 'payment/brippo_payments_paymentelement/payment_option_logos';
    const CONFIG_PAYMENT_METHODS_AVAILABLE      = 'payment/brippo_payments_paymentelement/payment_methods_available';
    const CONFIG_PAYMENT_METHODS                = 'payment/brippo_payments_paymentelement/payment_methods';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canRefundInvoicePartial = true;
}
