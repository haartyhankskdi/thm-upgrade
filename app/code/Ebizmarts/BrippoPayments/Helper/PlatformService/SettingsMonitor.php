<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SettingsMonitor extends PlatformService
{
    const PARAM_SETTINGS_PAYLOAD = 'settings';
    const PARAM_DISABLED_PM = 'disabled_pm';
    const PARAM_INTEGRATION = 'integration';
    const PARAM_LIVE_MODE = 'live_mode';
    const PARAM_URL = 'store_url';

    /**
     * @param string $scope
     * @param int $scopeId
     * @param bool $liveMode
     * @param string $settingsJson
     * @param array $disabledPM
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendNotification(string $scope, int $scopeId, bool $liveMode, $settingsJson, $disabledPM)
    {
        $accountId = $this->dataHelper->getAccountId($scopeId, $liveMode, $scope);
        $storeDomain = $this->dataHelper->getStoreDomain();

        $params = [
            self::PARAM_ACCOUNT_ID => $accountId,
            self::PARAM_SETTINGS_PAYLOAD => $settingsJson,
            self::PARAM_DISABLED_PM => $disabledPM,
            self::PARAM_INTEGRATION => 'magento',
            self::PARAM_LIVE_MODE => $liveMode,
            self::PARAM_URL => $storeDomain
        ];

        $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_URI_SETTINGS_MONITOR,
            $params
        );
    }
}
