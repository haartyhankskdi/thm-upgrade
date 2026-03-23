<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class Express extends PaymentMethod
{
    const METHOD_CODE                               = 'brippo_payments_express';
    const XML_PATH_ACTIVE                           = 'payment/brippo_payments_express/active';
    const XML_PATH_STORE_CONFIG_LOCATION            = 'payment/brippo_payments_express/location';
    const XML_PATH_STORE_CONFIG_WALLETS             = 'payment/brippo_payments_express/wallets';
    const XML_PATH_STORE_PRODUCT_PAGE_BEHAVIOR      = 'payment/brippo_payments_express/product_page_behavior';
    const XML_PATH_PRODUCT_MAXIMUM_AMOUNT     = 'payment/brippo_payments_express/product_maximum_amount';
    const XML_PATH_PRODUCT_MINIMUM_AMOUNT    = 'payment/brippo_payments_express/product_minimum_amount';
    const XML_PATH_CART_MAXIMUM_AMOUNT     = 'payment/brippo_payments_express/cart_maximum_amount';
    const XML_PATH_CART_MINIMUM_AMOUNT    = 'payment/brippo_payments_express/cart_minimum_amount';
    const XML_PATH_MINICART_MAXIMUM_AMOUNT     = 'payment/brippo_payments_express/minicart_maximum_amount';
    const XML_PATH_MINICART_MINIMUM_AMOUNT    = 'payment/brippo_payments_express/minicart_minimum_amount';
    const XML_PATH_STORE_PRODUCT_PAGE_CATEGORIES    = 'payment/brippo_payments_express/product_page_allowed_categories';
    const XML_PATH_STORE_BUSINESS_NAME              = 'payment/brippo_payments_express/business_name';
    const XML_PATH_BUTTON_TYPE                      = 'payment/brippo_payments_express/button_type';
    const XML_PATH_BUTTON_THEME                     = 'payment/brippo_payments_express/button_theme';
    const XML_PATH_BUTTON_HEIGHT                    = 'payment/brippo_payments_express/button_height';
    const XML_PATH_CAPTURE_METHOD                   = 'payment/brippo_payments_express/capture_method';
    const XML_PATH_STATUS_TRIGGERING_CAPTURE        = 'payment/brippo_payments_express/status_triggering_capture';
    const XML_PATH_CURRENCY_MODE                    = 'payment/brippo_payments_express/currency_mode';
    const XML_PATH_CHECKOUT_LOCATION                = 'payment/brippo_payments_express/checkout_location';
    const XML_PATH_CHECKOUT_EMAIL                   = 'payment/brippo_payments_express/checkout_email';
    const XML_PATH_COUPON_CODE                      = 'payment/brippo_payments_express/coupon_code';
    const XML_PATH_COUPON_CODE_NOTE                 = 'payment/brippo_payments_express/coupon_code_note';
    const XML_PATH_CHECKOUT_BUTTON                  = 'payment/brippo_payments_express/checkout_button';
    const XML_PATH_BLOCKED_SHIPPING_METHODS         = 'payment/brippo_payments_express/blocked_shipping_methods';
    const XML_PATH_REQUEST_AGREEMENTS               = 'payment/brippo_payments_express/request_agreements';
    const XML_PATH_PRODUCT_PLACEMENT                = 'payment/brippo_payments_express/product_button_layout_block';
    const XML_PATH_PRODUCT_SHOW_OR                  = 'payment/brippo_payments_express/product_button_or';
    const XML_PATH_CHECKOUT_PLACEMENT               = 'payment/brippo_payments_express/checkout_button_layout_block';
    const XML_PATH_CHECKOUT_VALIDATION_MODE         = 'payment/brippo_payments_express/checkout_form_validation_mode';

    const XML_PATH_MINICART_PLACEMENT               = 'payment/brippo_payments_express/minicart_button_layout_block';
    const XML_PATH_MINICART_SHOW_OR                 = 'payment/brippo_payments_express/minicart_button_or';


    const DEFAULT_PRODUCT_PLACEMENT_BLOCK           = '.box-tocart .actions';
    const DEFAULT_MINICART_PLACEMENT_BLOCK          = '#minicart-content-wrapper > div.block-content > div:nth-child(4) > div';


    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canRefundInvoicePartial = true;
}
