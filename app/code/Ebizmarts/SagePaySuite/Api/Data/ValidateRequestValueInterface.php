<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface ValidateRequestValueInterface
{
    public const OFFSET_TRIM_ZERO              = 0;
    public const START_POSITION_TRIM           = 1;
    public const STRING_LENGTH_TWO             = 2;
    public const STRING_LENGTH_TEN             = 10;
    public const STRING_LENGTH_NINETEEN        = 19;
    public const STRING_LENGTH_TWENTY          = 20;
    public const STRING_LENGTH_FORTY           = 40;
    public const ADDRESS_LENGTH                = 50;
    public const SHIPPING_DETAILS              = 'shippingDetails';
    public const BILLING_ADDRESS               = 'billingAddress';
    public const ADDRESS_1                     = 'address1';
    public const SHIPPING_ADDRESS_1            = 'shippingAddress1';
    public const TRANSACTION_TYPE              = 'transactionType';
    public const PAYMENT_METHOD                = 'paymentMethod';
    public const CARD                          = 'card';
    public const MERCHANT_SESSION_KEY          = 'merchantSessionKey';
    public const CARD_IDENTIFIER               = 'cardIdentifier';
    public const SAVE_TOKEN                    = 'save';
    public const REUSABLE_TOKEN                = 'reusable';
    public const VENDOR_TX_CODE                = 'vendorTxCode';
    public const ORDER_DESCRIPTION             = 'description';
    public const CUSTOMER_FIRST_NAME           = 'customerFirstName';
    public const CUSTOMER_LAST_NAME            = 'customerLastName';
    public const AVS_CVC_CHECK                 = 'applyAvsCvcCheck';
    public const REFERRED_ID                   = 'referrerId';
    public const CUSTOMER_EMAIL                = 'customerEmail';
    public const CUSTOMER_PHONE                = 'customerPhone';
    public const ENTRY_METHOD                  = 'entryMethod';
    public const APPLY_3D_SECURE               = 'apply3DSecure';
    public const BILLING_CITY                  = 'city';
    public const POSTAL_CODE                   = 'postalCode';
    public const COUNTRY                       = 'country';
    public const STATE                         = 'state';
    public const RECIPIENT_FIRST_NAME          = 'recipientFirstName';
    public const RECIPIENT_LAST_NAME           = 'recipientLastName';
    public const SHIPPING_CITY                 = 'shippingCity';
    public const SHIPPING_POSTAL_CODE          = 'shippingPostalCode';
    public const SHIPPING_COUNTRY              = 'shippingCountry';
    public const SHIPPING_STATE                = 'shippingState';
    public const ENTRY_METHOD_TELEPHONE_ORDER  = 'TelephoneOrder';
    public const ENTRY_METHOD_ECOMMERCE        = 'Ecommerce';
    public const COUNTRY_US                    = 'US';
    public const COUNTRY_IE                    = 'IE';
    public const COUNTRY_HK                    = 'HK';
    public const UNASSIGNED_POSTAL_CODE        = "000";
    public const STATUS_DETAIL                 = 'statusDetail';
}
