<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ThirdParty;

use Magento\Framework\Module\Manager;

class ModuleChecker
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function isAmastyFacebookPixelEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_FacebookPixel');
    }

    public function isAmastyGdprCookieFacebookPixelSampleDataEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_GdprCookieFacebookPixelSampleData');
    }
}
