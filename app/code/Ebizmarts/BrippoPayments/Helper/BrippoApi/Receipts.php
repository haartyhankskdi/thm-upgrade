<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class Receipts extends Service
{
    const API_URI = 'receipt/';

    /**
     * @param $liveMode
     * @param $stripeAccountId
     * @param $chargeId
     * @return array
     * @throws LocalizedException
     */
    public function get($liveMode, $stripeAccountId, $chargeId): array
    {
        $serviceUrl = self::SERVICE_URL . 'v2/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . self::API_URI
            . $stripeAccountId . '/'
            . $chargeId;

        return $this->curlGetRequest($serviceUrl);
    }

    /**
     * @param $receiptNumber
     * @return string
     */
    public function normalizeReceiptNumber($receiptNumber): string
    {
        return str_pad((string)$receiptNumber, 10, '0', STR_PAD_LEFT);
    }
}
