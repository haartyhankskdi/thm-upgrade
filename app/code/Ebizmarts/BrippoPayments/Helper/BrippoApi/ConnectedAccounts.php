<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class ConnectedAccounts extends Service
{
    const API_URI = 'connected-account/';

    /**
     * @param $liveMode
     * @param $accountId
     * @return array
     * @throws LocalizedException
     */
    public function get($liveMode, $accountId): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . ($liveMode ? self::PARAM_MODE_LIVE : self::PARAM_MODE_TEST) . '/'
            . self::API_URI . $accountId;

        return $this->curlGetRequest($serviceUrl);
    }
}
