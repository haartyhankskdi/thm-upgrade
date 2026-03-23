<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\Framework\Setup\Declaration\Schema\FileSystem\XmlReader;

use Amasty\Base\Model\Uninstall\Registry;
use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Module\Dir\Reader as FrameworkDirReader;

/**
 * Remove db_schema.xml of extensions on uninstallation from global scope for emulate DB comparison.
 *
 * @since 1.21.0
 */
class SchemaUninstallPlugin
{
    /**
     * @var Registry
     */
    private Registry $unistallRegistry;

    /**
     * @var FrameworkDirReader
     */
    private FrameworkDirReader $frameworkDirReader;

    public function __construct(
        Registry $unistallRegistry,
        FrameworkDirReader $frameworkDirReader
    ) {
        $this->unistallRegistry = $unistallRegistry;
        $this->frameworkDirReader = $frameworkDirReader;
    }

    /**
     * @param FileResolverByModule $subject
     * @param array $result
     * @param string $filename
     * @param string $scope
     * @return array
     */
    public function afterGet(FileResolverByModule $subject, array $result, $filename, $scope): array
    {
        if ($filename === 'db_schema.xml'
            && $scope === FileResolverByModule::ALL_MODULES
        ) {
            // Remove modules which on uninstallation process
            foreach ($this->unistallRegistry->getModules() as $module) {
                $path = $this->frameworkDirReader->getModuleDir('etc', $module) . '/' . $filename;
                unset($result[$path]);
            }
        }

        return $result;
    }
}
