<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class Stats extends Service
{
    const UNCAPTURED_PAYMENTS_API_URI = 'uncaptured-payments-count';

    /**
     * @param $id
     * @param $liveMode
     * @return array
     * @throws LocalizedException
     */
    public function getUncapturedPaymentsCount($id, $liveMode): array
    {
        if (empty($id)) {
            return null;
        }
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/' . $id . '/'
            . self::UNCAPTURED_PAYMENTS_API_URI;

        return $this->curlGetRequest($serviceUrl);
    }
}
