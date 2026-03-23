<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Plugin\GA4\ViewModel\GtmViewModel;

use Amasty\GoogleConsentMode\Model\ConfigProvider;

class ConsentModeConfig
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsConsentModeEnabled($subject, bool $result): bool
    {
        return $this->configProvider->isConsentModeEnabled() ?? $result;
    }
}
