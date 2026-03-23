<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Model\ThirdParty;

use Magento\Framework\Module\Manager;

class ModuleChecker
{
    public function __construct(
        private readonly Manager $moduleManager
    ) {
    }

    public function isAmastyPixelBingEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_PixelBing');
    }

    public function isAmastyGdprCookieEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_GdprCookie');
    }
}
