<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Stripe extends AbstractHelper
{
    public const ACCOUNT_CAPABILITY_CARD_PAYMENTS = 'card_payments';
    public const PAYMENT_INTENT_STATUS_CANCELED = 'canceled';
    public const PAYMENT_INTENT_STATUS_PROCESSING = 'processing';
    public const PAYMENT_INTENT_STATUS_REQUIRES_ACTION = 'requires_action';
    public const PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE = 'requires_capture';
    public const PAYMENT_INTENT_STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';
    public const PAYMENT_INTENT_STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    public const PAYMENT_INTENT_STATUS_SUCCEEDED = 'succeeded';

    public const METADATA_KEY_BILLING_ADDRESS = 'Billing Address';
    public const METADATA_KEY_BILLING_NAME = 'Billing Name';
    public const METADATA_KEY_BILLING_PHONE = 'Billing Phone';
    public const METADATA_KEY_BILLING_EMAIL = 'Billing Email';
    public const METADATA_KEY_IS_SOFT_FAIL_RECOVERY = 'Is Soft Fail Recovery';
    public const METADATA_KEY_QUOTE_ID = 'Quote';
    public const METADATA_KEY_NOTIFICATION_NUMBER = 'Notification Number';
    public const METADATA_KEY_SOURCE_LOCATION = 'Source';
    public const METADATA_KEY_WALLET_PROVIDER = 'Provider';
    public const METADATA_KEY_PAYMENT_METHOD_CODE = 'Integration';
    public const METADATA_KEY_EXTENSION_SIGNATURE = 'Extension Signature';
    public const METADATA_KEY_ORDER_ID = 'Order ID';
    public const METADATA_KEY_CUSTOMER_EMAIL = 'Customer Email';
    public const METADATA_KEY_ACCOUNT_ID = 'Account ID';
    public const METADATA_KEY_MAGENTO_EDITION = 'Magento Edition';
    public const METADATA_KEY_MAGENTO_VERSION = 'Magento Version';
    public const METADATA_KEY_CC_FINGERPRINT = 'CC Fingerprint';
    public const METADATA_KEY_RECOVER_SOURCE = 'Recover Source';
    public const METADATA_KEY_RECOVER_MANUAL = 'Recover Manual';

    public const METADATA_KEY_RECOVERED_FROM_PAYMENT_METHOD = 'Recovered From Payment Method';
    public const METADATA_KEY_RADAR_RISK = 'Radar Risk';
    public const METADATA_KEY_RADAR_SCORE = 'Radar Score';
    public const METADATA_KEY_STREET_CHECK = 'Street Check';
    public const METADATA_KEY_ZIP_CHECK = 'Zip Check';
    public const METADATA_KEY_CVC_CHECK = 'CVC Check';
    public const METADATA_KEY_STORE_URL = 'Store URL';
    public const METADATA_KEY_IP_ADDRESS = 'IP Address';
    public const METADATA_KEY_USER_AGENT = 'User Agent';
    public const METADATA_KEY_BRIPPO_UNIQUE_ID = 'Brippo UID';

    public const PARAM_ACTIVE = 'active';
    public const PARAM_AMOUNT = 'amount';
    public const PARAM_AMOUNT_CAPTURED = 'amount_captured';
    public const PARAM_AMOUNT_REFUNDED = 'amount_refunded';
    public const PARAM_BUSINESS_PROFILE = 'business_profile';
    public const PARAM_CAPABILITIES = 'capabilities';
    public const PARAM_CAPTURE_BEFORE = 'capture_before';
    public const PARAM_CARD = 'card';
    public const PARAM_CARD_PRESENT = 'card_present';
    public const PARAM_CHARGES_ENABLED = 'charges_enabled';
    public const PARAM_CHECKS = 'checks';
    public const PARAM_CHECKS_ADDRESS_1 = 'address_line1_check';
    public const PARAM_CHECKS_CVC = 'cvc_check';
    public const PARAM_CHECKS_POSTAL_CODE = 'address_postal_code_check';
    public const PARAM_CHECKS_ZIP = 'address_zip_check';
    public const PARAM_CLIENT_SECRET = 'client_secret';
    public const PARAM_CODE = 'code';
    public const PARAM_COUNTRY = 'country';
    public const PARAM_CURRENCY = 'currency';
    public const PARAM_DEFAULT_CURRENCY = 'default_currency';
    public const PARAM_DESTINATION_PAYMENT = 'destination_payment';
    public const PARAM_DETAILS_SUBMITTED = 'details_submitted';
    public const PARAM_EMAIL = 'email';
    public const PARAM_FAILURE_MESSAGE = 'failure_message';
    public const PARAM_ID = 'id';
    public const PARAM_LAST_PAYMENT_ERROR = 'last_payment_error';
    public const PARAM_LATEST_CHARGE = 'latest_charge';
    public const PARAM_METADATA = 'metadata';
    public const PARAM_MOTO = 'moto';
    public const PARAM_NAME = 'name';
    public const PARAM_NETWORK_STATUS = 'network_status';
    public const PARAM_OBJECT = 'object';
    public const PARAM_OUTCOME = 'outcome';
    public const PARAM_PAYMENT_METHOD_DETAILS = 'payment_method_details';
    public const PARAM_PAYMENT_METHODS = 'payment_methods';
    public const PARAM_PAYOUTS_ENABLED = 'payouts_enabled';
    public const PARAM_RESULT = 'result';
    public const PARAM_REVERSALS = 'reversals';
    public const PARAM_RISK_LEVEL = 'risk_level';
    public const PARAM_SELLER_MESSAGE = 'seller_message';
    public const PARAM_SOURCE = 'source';
    public const PARAM_SOURCE_REFUND = 'source_refund';
    public const PARAM_STATUS = 'status';
    public const PARAM_THREE_D_SECURE = 'three_d_secure';
    public const PARAM_TRANSFER = 'transfer';
    public const PARAM_TYPE = 'type';
    public const PARAM_URL = 'url';
    public const PARAM_STATEMENT_DESCRIPTOR = 'statement_descriptor';
    public const PARAM_WALLET = 'wallet';

    public const CAPTURE_METHOD_AUTOMATIC = 'automatic';
    public const CAPTURE_METHOD_MANUAL = 'manual';

    public const CONFIRM_ERROR_CODE_PAYMENT_INTENT_AUTHENTICATION_FAILURE  = 'payment_intent_authentication_failure';
    public const CONFIRM_ERROR_CODE_TRY_AGAIN_LATER                        = 'try_again_later';
    public const CONFIRM_ERROR_CODE_INVALID_AMOUNT                         = 'invalid_amount';
    public const CONFIRM_ERROR_CODE_INSUFFICIENT_FUNDS                     = 'insufficient_funds';
    public const CONFIRM_ERROR_CODE_TRANSACTION_NOT_ALLOWED                = 'transaction_not_allowed';
    public const CONFIRM_ERROR_CODE_CARD_VELOCITY_EXCEEDED                 = 'card_velocity_exceeded';
    public const CONFIRM_ERROR_CODE_DO_NOT_HONOR                           = 'do_not_honor';
    public const CONFIRM_ERROR_CODE_PROCESSING_ERROR                       = 'processing_error';
    public const CONFIRM_ERROR_CODE_INCORRECT_CVC                          = 'incorrect_cvc';
    public const CONFIRM_ERROR_CODE_CARD_DECLINED                          = 'card_declined';
    public const CONFIRM_ERROR_CODE_EXPIRED_CARD                           = 'expired_card';

    /**
     * @param $configSetting
     * @return string
     */
    public function getCaptureMethod($configSetting): string
    {
        if (strpos($configSetting, 'manual') !== false) {
            return self::CAPTURE_METHOD_MANUAL;
        }
        return $configSetting;
    }

    /**
     * @param $amount
     * @param $currency
     * @return float|int
     */
    public function convertMagentoAmountToStripeAmount($amount, $currency)
    {
        if (empty($amount) || !is_numeric($amount) || $amount < 0) {
            return 0;
        }

        $cents = 100;
        if ($this->isZeroDecimal($currency)) {
            $cents = 1;
        }

        return round($amount * $cents);
    }

    /**
     * @param $price
     * @param $currency
     * @return string
     */
    public function convertStripeAmountToMagentoAmount($price, $currency = null): string
    {
        if (!$this->isZeroDecimal($currency)) {
            $price /= 100;
        }

        return $price;
    }

    /**
     * @param $currency
     * @return bool
     */
    protected function isZeroDecimal($currency): bool
    {
        return in_array(strtolower($currency), [
            'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
            'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof']);
    }

    /**
     * @param array $charge
     * @return mixed|null
     */
    public function getCardData(array $charge)
    {
        if (empty($charge)) {
            return null;
        }

        if (!empty($charge[self::PARAM_SOURCE])) {
            if (isset($charge[self::PARAM_SOURCE][self::PARAM_OBJECT]) &&
                $charge[self::PARAM_SOURCE][self::PARAM_OBJECT] === 'card') {
                return $charge[self::PARAM_SOURCE];
            }

            if (isset($charge[self::PARAM_SOURCE][self::PARAM_TYPE]) &&
                $charge[self::PARAM_SOURCE][self::PARAM_TYPE] === 'three_d_secure') {
                return $charge[self::PARAM_SOURCE][self::PARAM_THREE_D_SECURE];
            }
        }

        if (!empty($charge[self::PARAM_PAYMENT_METHOD_DETAILS][self::PARAM_CARD])) {
            return $charge[self::PARAM_PAYMENT_METHOD_DETAILS][self::PARAM_CARD];
        }

        if (!empty($charge[self::PARAM_PAYMENT_METHOD_DETAILS][self::PARAM_CARD_PRESENT])) {
            return $charge[self::PARAM_PAYMENT_METHOD_DETAILS][self::PARAM_CARD_PRESENT];
        }

        if (!empty($charge[self::PARAM_SOURCE][self::PARAM_CARD])) {
            return $charge[self::PARAM_SOURCE][self::PARAM_CARD];
        }

        return null;
    }

    /**
     * @param array $chargeData
     * @return mixed|string
     */
    public function getStreetCheck(array $chargeData)
    {
        $card = $this->getCardData($chargeData);

        if (!empty($card) && !empty($card[self::PARAM_CHECKS][self::PARAM_CHECKS_ADDRESS_1])) {
            return $card[self::PARAM_CHECKS][self::PARAM_CHECKS_ADDRESS_1];
        }

        if (!empty($card) && !empty($card[self::PARAM_CHECKS_ADDRESS_1])) {
            return $card[self::PARAM_CHECKS_ADDRESS_1];
        }

        return 'unchecked';
    }

    /**
     * @param array $chargeData
     * @return mixed|string
     */
    public function getZipCheck(array $chargeData)
    {
        $card = $this->getCardData($chargeData);

        if (!empty($card) && !empty($card[self::PARAM_CHECKS][self::PARAM_CHECKS_POSTAL_CODE])) {
            return $card[self::PARAM_CHECKS][self::PARAM_CHECKS_POSTAL_CODE];
        }

        if (!empty($card) && !empty($card[self::PARAM_CHECKS_ZIP])) {
            return $card[self::PARAM_CHECKS_ZIP];
        }

        return 'unchecked';
    }

    public function getCVCCheck(array $chargeData)
    {
        $card = $this->getCardData($chargeData);

        if (!empty($card) && !empty($card[self::PARAM_CHECKS][self::PARAM_CHECKS_CVC])) {
            return $card[self::PARAM_CHECKS][self::PARAM_CHECKS_CVC];
        }

        if (!empty($card) && !empty($card[self::PARAM_CHECKS_CVC])) {
            return $card[self::PARAM_CHECKS_CVC];
        }

        return 'unchecked';
    }

    /**
     * @param $paymentIntent
     * @return null|string
     */
    public function getTransferChargeIdFromPaymentIntent($paymentIntent)
    {
        if (isset($paymentIntent[self::PARAM_LATEST_CHARGE]
                [self::PARAM_TRANSFER][self::PARAM_DESTINATION_PAYMENT]
                [self::PARAM_ID]) && !empty($paymentIntent)
        ) {
            return $paymentIntent[self::PARAM_LATEST_CHARGE]
            [self::PARAM_TRANSFER][self::PARAM_DESTINATION_PAYMENT]
            [self::PARAM_ID];
        }
        return null;
    }

    /**
     * @param $paymentMethod
     * @return array
     */
    public function getCardFromPaymentMethod($paymentMethod): array
    {
        $card = [];
        if (!empty($paymentMethod) && isset($paymentMethod['card'])) {
            $card = $paymentMethod['card'];
        }
        return $card;
    }

    /**
     * @param $paymentMethod
     * @return string
     */
    public function getWalletNameFromPaymentMethod($paymentMethod): string
    {
        $walletProvider = 'Undefined';
        $card = $this->getCardFromPaymentMethod($paymentMethod);
        if (!empty($card)) {
            if (isset($card['wallet']['type'])) {
                switch ($card['wallet']['type']) {
                    case 'google_pay':
                        $walletProvider = 'Google Pay';
                        break;
                    case 'apple_pay':
                        $walletProvider = 'Apple Pay';
                        break;
                    case 'link':
                        $walletProvider = 'Link';
                        break;
                }
            }
        } elseif (!empty($paymentMethod['type'])) {
            $walletProvider = $paymentMethod['type'];
        }

        return $walletProvider;
    }
}
