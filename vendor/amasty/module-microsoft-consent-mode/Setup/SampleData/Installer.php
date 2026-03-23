<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Setup\SampleData;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\SampleData\InstallerInterface;

class Installer implements InstallerInterface
{
    /**
     * Flag to mark if installer was executed
     * to prevent re-installation of sample data
     * in one setup run.
     *
     * @var bool
     */
    private bool $executed = false;

    public function __construct(
        private readonly State $appState,
        private readonly array $installers = []
    ) {
    }

    public function install(): void
    {
        if ($this->executed === true) {
            return;
        }

        foreach ($this->installers as $installer) {
            $this->appState->emulateAreaCode(
                Area::AREA_FRONTEND,
                [$installer, 'install']
            );
        }
        $this->executed = true;
    }
}
