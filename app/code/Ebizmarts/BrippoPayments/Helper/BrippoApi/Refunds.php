<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class Refunds extends Service
{
    const API_URI = 'refund/';

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
}
