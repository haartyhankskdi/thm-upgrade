<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup;

use Amasty\GdprCookie\Model\ResourceModel\Cookie;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->uninstallTables($setup)
            ->uninstallConfigData($setup)
            ->uninstallFlagData($setup);
        $setup->endSetup();
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $tablesToDrop = [
            CookieConsent::STATUS_TABLE_NAME,
            CookieConsent::TABLE_NAME,
            Cookie::STORE_DATA_TABLE_NAME,
            Cookie::TABLE_NAME,
            CookieGroup::STORE_DATA_TABLE_NAME,
            CookieGroup::TABLE_NAME
        ];
        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }

        return $this;
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): self
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            "`path` LIKE 'amasty_gdprcookie%'"
        );

        return $this;
    }

    private function uninstallFlagData(SchemaSetupInterface $setup): self
    {
        $setup->getConnection()->delete(
            $setup->getTable('flag'),
            '`flag_code` LIKE \'am_gdpr_cookie_%\''
        );

        return $this;
    }
}
