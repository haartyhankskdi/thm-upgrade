<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Model\ThirdParty\ModuleChecker;
use Amasty\GdprCookieFacebookPixelSampleData\Setup\SampleData\Installer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallFbSampleData implements DataPatchInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleChecker
     */
    private $moduleChecker;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleChecker $moduleChecker
    ) {
        $this->objectManager = $objectManager;
        $this->moduleChecker = $moduleChecker;
    }

    public function apply(): InstallFbSampleData
    {
        if ($this->moduleChecker->isAmastyGdprCookieFacebookPixelSampleDataEnabled()
            && $this->moduleChecker->isAmastyFacebookPixelEnabled()
        ) {
            $this->getSampleDataInstaller()->install();
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    private function getSampleDataInstaller(): Installer
    {
        return $this->objectManager->get(Installer::class);
    }
}
