<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Plugin\GdprCookie\Model\GoogleAnalytics\IsGaCookieAllowed;

use Amasty\GoogleConsentMode\Model\ConfigProvider;
use Amasty\GdprCookie\Model\GoogleAnalytics\IsGaCookieAllowed;

class PreventCookieGtmRemove
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
    public function aroundExecute(IsGaCookieAllowed $subject, callable $proceed): bool
    {
        if (!$this->configProvider->isConsentModeEnabled()) {
            return $proceed();
        }

        return true;
    }
}
