<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\Customer\Model;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\FlagRegistry;
use Magento\Customer\Model\Session;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog;

/**
 * This plugin allows update internal policy compliance records
 * because the session ID is updated
 */
class SessionPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    /**
     * @var VisitorConsentLog
     */
    private $visitorConsentLog;

    /**
     * @var string
     */
    private $prevSessionId = '';

    public function __construct(
        Config $config,
        FlagRegistry $flagRegistry,
        VisitorConsentLog $visitorConsentLog
    ) {
        $this->config = $config;
        $this->flagRegistry = $flagRegistry;
        $this->visitorConsentLog = $visitorConsentLog;
    }

    public function beforeRegenerateId(Session $subject): void
    {
        if ($this->config->isModuleEnabled()
            && $this->flagRegistry->getFlagEnableSessionPlugin()
        ) {
            $this->prevSessionId = $subject->getSessionId();
        }
    }

    public function afterRegenerateId(Session $subject): void
    {
        if ($this->config->isModuleEnabled()
            && $this->flagRegistry->getFlagEnableSessionPlugin()
        ) {
            $sessionId = $subject->getSessionId();
            if ($this->prevSessionId != $sessionId) {
                $this->visitorConsentLog->updateSessionId($this->prevSessionId, $sessionId);
            }
        }
    }
}
