<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup\Patch\Data;

use Amasty\Gdpr\Setup\SampleData\Installer;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallGdprSampleData implements DataPatchInterface
{
    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var Installer
     */
    private $installer;

    public function __construct(
        ResourceInterface $moduleResource,
        Installer $installer
    ) {
        $this->moduleResource = $moduleResource;
        $this->installer = $installer;
    }

    public function apply(): InstallGdprSampleData
    {
        $setupDataVersion = $this->moduleResource->getDataVersion('Amasty_Gdpr');

        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if (!$setupDataVersion) {
            $this->installer->install();
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
}
