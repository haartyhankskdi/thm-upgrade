<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;

class ExpressCheckoutElement extends PaymentMethod
{
    const METHOD_CODE                            = 'brippo_payments_ece';
    const XML_PATH_ACTIVE                        = 'payment/brippo_payments_ece/active';
    const XML_PATH_STORE_BUSINESS_NAME           = 'payment/brippo_payments_ece/business_name';
    const XML_PATH_CAPTURE_METHOD                = 'payment/brippo_payments_ece/capture_method';
    const XML_PATH_STATUS_TRIGGERING_CAPTURE     = 'payment/brippo_payments_ece/status_triggering_capture';
    const XML_PATH_BLOCKED_SHIPPING_METHODS      = 'payment/brippo_payments_ece/blocked_shipping_methods';
    const XML_PATH_WALLETS_APPLE_PAY             = 'payment/brippo_payments_ece/wallets_apple_pay';
    const XML_PATH_WALLETS_APPLE_PAY_ORDER       = 'payment/brippo_payments_ece/wallets_applepay_order';
    const XML_PATH_WALLETS_GOOGLE_PAY            = 'payment/brippo_payments_ece/wallets_google_pay';
    const XML_PATH_WALLETS_GOOGLE_PAY_ORDER      = 'payment/brippo_payments_ece/wallets_googlepay_order';
    const XML_PATH_THEME                         = 'payment/brippo_payments_ece/theme';
    const XML_PATH_LOCATIONS_CART                = 'payment/brippo_payments_ece/locations_cart';
    const XML_PATH_LOCATIONS_CART_OR_SEPARATOR   = 'payment/brippo_payments_ece/locations_cart_or_separator';
    const XML_PATH_LOCATIONS_CART_PLACEMENT      = 'payment/brippo_payments_ece/locations_cart_placement_selector';
    const XML_PATH_LOCATIONS_CART_PLACEMENT_MODE = 'payment/brippo_payments_ece/locations_cart_placement_mode';
    const XML_PATH_LOCATIONS_CART_MIN_AMOUNT   = 'payment/brippo_payments_ece/locations_cart_min_amount';
    const XML_PATH_LOCATIONS_CART_MAX_AMOUNT   = 'payment/brippo_payments_ece/locations_cart_max_amount';
    const XML_PATH_LOCATIONS_MINICART            = 'payment/brippo_payments_ece/locations_minicart';
    const XML_PATH_LOCATIONS_MINICART_OR_SEPARATOR   = 'payment/brippo_payments_ece/locations_minicart_or_separator';
    const XML_PATH_LOCATIONS_MINICART_MIN_AMOUNT   = 'payment/brippo_payments_ece/locations_minicart_min_amount';
    const XML_PATH_LOCATIONS_MINICART_MAX_AMOUNT   = 'payment/brippo_payments_ece/locations_minicart_max_amount';
    const XML_PATH_LOCATIONS_MINICART_PLACEMENT   = 'payment/brippo_payments_ece/locations_minicart_placement_selector';
    const XML_PATH_LOCATIONS_MINICART_PLACEMENT_MODE = 'payment/brippo_payments_ece/locations_minicart_placement_mode';
    const XML_PATH_LOCATIONS_MINICART_EVENT_TYPE = 'payment/brippo_payments_ece/locations_minicart_event_type';
    const XML_PATH_LOCATIONS_PRODUCT             = 'payment/brippo_payments_ece/locations_product';
    const XML_PATH_LOCATIONS_PRODUCT_OR_SEPARATOR   = 'payment/brippo_payments_ece/locations_product_or_separator';
    const XML_PATH_LOCATIONS_PRODUCT_PLACEMENT   = 'payment/brippo_payments_ece/locations_product_placement_selector';
    const XML_PATH_LOCATIONS_PRODUCT_PLACEMENT_MODE = 'payment/brippo_payments_ece/locations_product_placement_mode';
    const XML_PATH_LOCATIONS_PRODUCT_MIN_AMOUNT   = 'payment/brippo_payments_ece/locations_product_min_amount';
    const XML_PATH_LOCATIONS_PRODUCT_MAX_AMOUNT   = 'payment/brippo_payments_ece/locations_product_max_amount';
    const XML_PATH_LOCATIONS_CHECKOUT            = 'payment/brippo_payments_ece/locations_checkout';
    const XML_PATH_LOCATIONS_CHECKOUT_OR_SEPARATOR   = 'payment/brippo_payments_ece/locations_checkout_or_separator';
    const XML_PATH_LOCATIONS_CHECKOUT_TITLE   = 'payment/brippo_payments_ece/locations_checkout_title';
    const XML_PATH_LOCATIONS_CHECKOUT_PLACEMENT   = 'payment/brippo_payments_ece/locations_checkout_placement_selector';
    const XML_PATH_LOCATIONS_CHECKOUT_PLACEMENT_MODE = 'payment/brippo_payments_ece/locations_checkout_placement_mode';
    const XML_PATH_LOCATIONS_CHECKOUT_MIN_AMOUNT   = 'payment/brippo_payments_ece/locations_checkout_min_amount';
    const XML_PATH_LOCATIONS_CHECKOUT_MAX_AMOUNT   = 'payment/brippo_payments_ece/locations_checkout_max_amount';
    const XML_PATH_WALLETS_LINK                  = 'payment/brippo_payments_ece/wallets_link';
    const XML_PATH_WALLETS_LINK_ORDER            = 'payment/brippo_payments_ece/wallets_link_order';
    const XML_PATH_LAYOUT_MAX_ROWS               = 'payment/brippo_payments_ece/layout_max_rows';
    const XML_PATH_LAYOUT_MAX_COLS               = 'payment/brippo_payments_ece/layout_max_columns';
    const XML_PATH_LAYOUT_OVERFLOW               = 'payment/brippo_payments_ece/layout_overflow';
    const XML_PATH_STYLE_BUTTONS_HEIGHT          = 'payment/brippo_payments_ece/style_buttons_height';
    const XML_PATH_STYLE_APPLE_PAY               = 'payment/brippo_payments_ece/style_apple_pay';
    const XML_PATH_TYPE_APPLE_PAY                = 'payment/brippo_payments_ece/type_apple_pay';
    const XML_PATH_STYLE_GOOGLE_PAY              = 'payment/brippo_payments_ece/style_google_pay';
    const XML_PATH_TYPE_GOOGLE_PAY               = 'payment/brippo_payments_ece/type_google_pay';
    const XML_PATH_STYLE_BUTTONS_CORNER_RADIUS   = 'payment/brippo_payments_ece/style_buttons_corner_radius';
    const XML_PATH_STYLE_FONT_SIZE_BASE          = 'payment/brippo_payments_ece/style_font_size_base';
    const XML_PATH_BLOCKED_CUSTOMER_GROUPS       = 'payment/brippo_payments_ece/blocked_customer_groups';
    const CONFIG_PATH_PICKUP_INPUT_VALUES        = 'payment/brippo_payments_ece/pick_up_input_values';
    const XML_PATH_LOCATIONS_CHECKOUT_LIST       = 'payment/brippo_payments_ece/locations_checkout_list';
    const XML_PATH_LOCATIONS_CHECKOUT_MIN_AMOUNT_LIST = 'payment/brippo_payments_ece/locations_checkout_min_amount_list';
    const XML_PATH_LOCATIONS_CHECKOUT_MAX_AMOUNT_LIST = 'payment/brippo_payments_ece/locations_checkout_max_amount_list';

    protected $_code                    = self::METHOD_CODE;
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canRefundInvoicePartial = true;
}
