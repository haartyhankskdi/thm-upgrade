<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\Config as SessionConfig;
use Magento\Store\Model\ScopeInterface;

class ClearVisitorConsentLog
{
    public const SECONDS_IN_DAY = 86400;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var VisitorConsentLogResource
     */
    private $visitorConsentLogResource;

    public function __construct(
        VisitorConsentLogResource $visitorConsentLogResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->visitorConsentLogResource = $visitorConsentLogResource;
        $this->scopeConfig = $scopeConfig;
    }

    private function getCleanTime(): int
    {
        return self::SECONDS_IN_DAY + (int)$this->scopeConfig->getValue(
            SessionConfig::XML_PATH_COOKIE_LIFETIME,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function clearLog()
    {
        $cleanTime = $this->getCleanTime();

        return $this->visitorConsentLogResource->clear($cleanTime);
    }
}
