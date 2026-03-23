<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ThirdParty;

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

    public function isAmastyFaqEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_Faq');
    }

    public function isAmastyGdprFaqSampleDataEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_GdprFaqSampleData');
    }
}
