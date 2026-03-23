<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use DateTime;
use Magento\Framework\Exception\LocalizedException;

class Analytics extends PlatformService
{
    const EVENT_TYPE_PAYMENT_REQUEST = 'paymentRequest';

    /**
     * @param string $scope
     * @param int $scopeId
     * @param bool $liveMode
     * @param string $eventType
     * @param string $environment
     * @param string $message
     * @return void
     * @throws LocalizedException
     */
    public function sendAnalytic(
        string $scope,
        int $scopeId,
        string $eventType,
        string $environment,
        string $message = ""
    ): void
    {
        $liveMode = $this->dataHelper->isLiveMode($scopeId, $scope);
        $accountId = $this->dataHelper->getAccountId($scopeId, $liveMode, $scope);
        $storeDomain = $this->dataHelper->getStoreDomain();
        $now = new DateTime('now');
        $date = $now->format('Y-m-d');

        $params = [
            self::PARAM_STRIPE_ACCOUNT_ID => $accountId,
            self::PARAM_LIVEMODE2 => $liveMode,
            self::PARAM_ENVIRONMENT => $environment,
            self::PARAM_EVENT_TYPE => $eventType,
            self::PARAM_MESSAGE => $message,
            self::PARAM_DATE => $date,
            self::PARAM_STORE_URL2 => $storeDomain
        ];

        $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_URI_ANALYTICS,
            $params
        );
    }
}
