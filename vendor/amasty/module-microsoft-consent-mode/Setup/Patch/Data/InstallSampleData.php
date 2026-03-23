<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Setup\Patch\Data;

use Amasty\MicrosoftConsentMode\Model\ThirdParty\ModuleChecker;
use Amasty\MicrosoftConsentMode\Setup\SampleData\Installer;
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
        if ($this->moduleChecker->isAmastyGdprCookieEnabled()
            && $this->moduleChecker->isAmastyPixelBingEnabled()
        ) {
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
