<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class Terminals extends Service
{
    const API_URI = 'terminal/';

    /**
     * @param $liveMode
     * @param $accountId
     * @return array
     * @throws LocalizedException
     */
    public function list($liveMode, $accountId): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI
            . 'by-account/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . $accountId;

        return $this->curlGetRequest($serviceUrl);
    }

    /**
     * @param $liveMode
     * @param $terminalId
     * @return array
     * @throws LocalizedException
     */
    public function get($liveMode, $terminalId): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI
            . 'by-id/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . $terminalId;

        return $this->curlGetRequest($serviceUrl);
    }

    /**
     * @param $liveMode
     * @param string $readerId
     * @param string $paymentIntentId
     * @param bool $isMoto
     * @return array
     * @throws LocalizedException
     */
    public function processPaymentIntent($liveMode, string $readerId, string $paymentIntentId, bool $isMoto): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/' . self::API_URI
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . 'payment-intent/' . $paymentIntentId
            . '/process';

        $params = [
            self::PARAM_READER_ID => $readerId,
            self::PARAM_PROCESS_CONFIG => [
                'moto' => $isMoto,
                'allow_redisplay' => 'always',
                'enable_customer_cancellation' => true,
                'skip_tipping' => true
            ]
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }
}
