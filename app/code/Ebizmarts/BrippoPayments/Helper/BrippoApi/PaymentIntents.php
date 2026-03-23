<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Ebizmarts\BrippoPayments\Exception\BrippoApiException;
use Magento\Framework\Exception\LocalizedException;

class PaymentIntents extends Service
{
    const API_URI = 'payment-intent/';
    const TIMELINE_ITEM_TYPE_ORDER_STATUS = 'magento_status';
    const TIMELINE_ITEM_TYPE_ORDER_STATUS_PAYPAL = 'magento_status_paypal';

    /**
     * @param $amount
     * @param $currency
     * @param $captureMethod
     * @param $accountId
     * @param $accountCountry
     * @param $metadata
     * @param $description
     * @param $livemode
     * @param $card
     * @param string $paymentMethod
     * @param $threeDSecure
     * @param $statementDescriptorSuffix
     * @param bool $automaticPaymentMethods
     * @param $paymentMethodTypes
     * @param $setupFutureUsage
     * @return array
     * @throws LocalizedException
     */
    public function create(
        $amount,
        $currency,
        $captureMethod,
        $accountId,
        $accountCountry,
        $metadata,
        $description,
        $livemode,
        $card,
        $orderId,
        string $paymentMethod = "",
        $threeDSecure = "",
        $statementDescriptorSuffix = "",
        bool $automaticPaymentMethods = true,
        $paymentMethodTypes = null,
        $setupFutureUsage = null
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v6/' . self::API_URI;
        $params = [
            "amount" => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
            'currency' => strtolower($currency),
            "capture_method" => $captureMethod,
            "on_behalf_of" => $accountId,
            "country" => $accountCountry,
            "metadata" => $metadata,
            "description" => $description,
            "livemode" => $livemode,
            "card" => $card,
            "payment_method" => $paymentMethod,
            "three_d_secure" => $threeDSecure,
            "statement_descriptor_suffix" => $statementDescriptorSuffix,
            "automatic_payment_methods" => $automaticPaymentMethods,
            "payment_method_types" => $paymentMethodTypes,
            "setup_future_usage" => $setupFutureUsage
        ];

        return $this->curlPostRequest(
            $serviceUrl,
            $params,
        [
            'X-Correlation-Id' => $accountId . '_' . $orderId
        ]);
    }

    /**
     * @param $id
     * @param $liveMode
     * @return array
     * @throws LocalizedException
     */
    public function get($id, $liveMode): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . self::API_URI . $id;

        return $this->curlGetRequest($serviceUrl);
    }

    /**
     * @param string $paymentIntentId
     * @param $amount
     * @param string $currency
     * @param $livemode
     * @return array
     * @throws LocalizedException
     */
    public function update(
        string $paymentIntentId,
        $amount,
        string $currency,
        $livemode
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI  . $paymentIntentId . '/update/';
        $params = [
            "amount" => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
            "livemode" => $livemode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }

    /**
     * @param bool $liveMode
     * @param string $paymentIntentId
     * @param string $newOrderStatus
     * @param string $type
     * @param string $accountId
     * @param $orderId
     * @param array $additional_information
     * @return array
     * @throws LocalizedException
     */
    public function reportTimelineStatus(
        bool $liveMode,
        string $paymentIntentId,
        string $newOrderStatus,
        string $type,
        string $accountId,
        $orderId,
        array $additional_information = []
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . $this->getMode($liveMode) . '/'
            . self::API_URI  . $paymentIntentId
            . '/timeline-status/';
        $params = [
            "new_status" => $newOrderStatus,
            "type" => $type,
            "account_id" => $accountId,
            "order_id" => $orderId,
            'additional_information' => $additional_information
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }

    /**
     * @param string $paymentIntentId
     * @param $livemode
     * @param $amount
     * @param $currency
     * @return array
     * @throws LocalizedException
     */
    public function capture(string $paymentIntentId, $livemode, $amount, $currency): array
    {
        $serviceUrl = self::SERVICE_URL . 'v5/' . self::API_URI  . $paymentIntentId . '/capture/';
        $params = [
            self::PARAM_KEY_LIVEMODE => $livemode,
            self::PARAM_KEY_AMOUNT => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency)
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }

    /**
     * @param string $paymentIntentId
     * @param bool $livemode
     * @return array
     * @throws LocalizedException
     */
    public function cancel(
        string $paymentIntentId,
        bool $livemode
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI  . $paymentIntentId . '/cancel/';
        $params = [
            "livemode" => $livemode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }

    /**
     * @param string $paymentIntentId
     * @param string $descriptionTransfer
     * @param string $descriptionPaymentIntent
     * @param array $metadata
     * @param string $accountId
     * @param $livemode
     * @return array
     * @throws LocalizedException
     */
    public function updateTransferCharge(
        string $paymentIntentId,
        string $descriptionTransfer,
        string $descriptionPaymentIntent,
        array $metadata,
        string $accountId,
        $livemode
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v3/'
            . self::API_URI  . $paymentIntentId . '/update-with-transfer/';
        $params = [
            'description_transfer' => $descriptionTransfer,
            'description_payment_intent' => $descriptionPaymentIntent,
            'metadata' => $metadata,
            'on_bahalf_of' => $accountId,
            'livemode' => $livemode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }

    /**
     * @param string $accountId
     * @param string $paymentIntentId
     * @param $amount
     * @param string $currency
     * @param bool $liveMode
     * @return array
     * @throws LocalizedException
     */
    public function refund(
        string $accountId,
        string $paymentIntentId,
        $amount,
        string $currency,
        bool $liveMode
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI  . $paymentIntentId . '/refund/';
        $params = [
            "amount" => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
            "livemode" => $liveMode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }
}
