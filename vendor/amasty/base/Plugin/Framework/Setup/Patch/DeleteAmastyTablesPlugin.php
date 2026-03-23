<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\Framework\Setup\Patch;

use Amasty\Base\Model\Uninstall\DeclarationDbSchema;
use Magento\Framework\Setup\Patch\PatchApplier;

/**
 * @since 1.21.0
 */
class DeleteAmastyTablesPlugin
{
    /**
     * @var DeclarationDbSchema
     */
    private DeclarationDbSchema $schemaDelete;

    public function __construct(
        DeclarationDbSchema $schemaDelete
    ) {
        $this->schemaDelete = $schemaDelete;
    }

    /**
     * @see \Magento\Framework\Setup\Patch\PatchApplier::revertDataPatches
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRevertDataPatches(PatchApplier $subject, ?string $moduleName = null): void
    {
        if (!$moduleName || strpos($moduleName, 'Amasty_') !== 0) {
            return;
        }

        $this->schemaDelete->uninstallModule($moduleName);
    }
}
