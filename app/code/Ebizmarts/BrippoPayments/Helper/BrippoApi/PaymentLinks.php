<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class PaymentLinks extends Service
{
    const API_URI = 'payment-link/';

    /**
     * @param $amount
     * @param $currency
     * @param $orderIncrementId
     * @param $accountId
     * @param $afterCompletion
     * @param $hostedConfirmationMessage
     * @param $redirectUrl
     * @param $metadata
     * @param $livemode
     * @return array
     * @throws LocalizedException
     */
    public function create(
        $amount,
        $currency,
        $orderIncrementId,
        $accountId,
        $afterCompletion,
        $hostedConfirmationMessage,
        $redirectUrl,
        $metadata,
        $livemode
    ): array {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI;
        $params = [
            "amount" => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
            'currency' => strtolower($currency),
            "order_increment" => $orderIncrementId,
            "on_behalf_of" => $accountId,
            "after_completion" => $afterCompletion,
            "hosted_confirmation_message" => $hostedConfirmationMessage,
            "redirect_url" => $redirectUrl,
            "metadata" => $metadata,
            "livemode" => $livemode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
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
     * @param string $paymentLinkId
     * @param $livemode
     * @return array
     * @throws LocalizedException
     */
    public function cancel(string $paymentLinkId, $livemode): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI . $paymentLinkId . '/cancel/';
        $params = [
            "livemode" => $livemode
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }
}
