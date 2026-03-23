<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Model\ThirdParty;

use Magento\Framework\Module\Manager;

class ModuleChecker
{
    public function __construct(
        private readonly Manager $moduleManager
    ) {
    }

    public function isAmastyGdprCookieEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_GdprCookie');
    }
}
