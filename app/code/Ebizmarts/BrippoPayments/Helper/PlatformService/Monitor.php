<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Monitor extends PlatformService
{
    /**
     * @param string $scope
     * @param int $scopeId
     * @param bool $liveMode
     * @param string $paymentIntentId
     * @param $orderIncrementId
     * @param string $message
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendNotification(
        string $scope,
        int $scopeId,
        bool $liveMode,
        string $paymentIntentId,
        $orderIncrementId,
        string $message
    ) {
        $accountId = $this->dataHelper->getAccountId($scopeId, $liveMode, $scope);
        $hashKey = $this->dataHelper->getOauthHashKey();
        $storeDomain = $this->dataHelper->getStoreDomain();

        $params = [
            self::PARAM_ACCOUNT_ID => $accountId,
            self::PARAM_LIVEMODE => $liveMode,
            self::PARAM_HASH_KEY => $hashKey,
            self::PARAM_ORDER_ID => $orderIncrementId,
            self::PARAM_PAYMENT_INTENT_ID => $paymentIntentId,
            self::PARAM_STORE_URL => $storeDomain,
            self::PARAM_MESSAGE => $message
        ];

        $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_URI_MONITOR,
            $params
        );
    }
}
