<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class WebhookMapping extends PlatformService
{
    /**
     * @param string $scope
     * @param int $scopeId
     * @param bool $liveMode
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function check(string $scope, int $scopeId, bool $liveMode)
    {
        $accountId = $this->dataHelper->getAccountId($scopeId, $liveMode, $scope);
        $webhookUrl = $this->dataHelper->getWebhookUrl($scopeId);
        $hashKey = $this->dataHelper->getOauthHashKey();

        $params = [
            self::PARAM_ACCOUNT_ID => $accountId,
            self::PARAM_WEBHOOK_URL => $webhookUrl,
            self::PARAM_HASH_KEY => $hashKey
        ];

        $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_URI_WEBHOOK_MAPPING_CHECK,
            $params
        );
    }
}
