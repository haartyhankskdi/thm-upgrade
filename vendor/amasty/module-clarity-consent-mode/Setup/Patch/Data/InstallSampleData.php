<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Setup\Patch\Data;

use Amasty\ClarityConsentMode\Model\ThirdParty\ModuleChecker;
use Amasty\ClarityConsentMode\Setup\SampleData\Installer;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallSampleData implements DataPatchInterface
{
    public function __construct(
        private readonly Installer $sampleDataInstaller,
        private readonly ModuleChecker $moduleChecker
    ) {
    }

    public function apply(): void
    {
        if ($this->moduleChecker->isAmastyGdprCookieEnabled()) {
            $this->sampleDataInstaller->install();
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
