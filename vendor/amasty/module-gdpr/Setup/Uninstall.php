<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup;

use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStore as ResourceConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\Consent as ResourceConsent;
use Amasty\Gdpr\Model\ResourceModel\ActionLog;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest;
use Amasty\Gdpr\Model\ResourceModel\Policy;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent;
use Amasty\Gdpr\Model\ResourceModel\WithConsent;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this
            ->uninstallTables($setup)
            ->uninstallConfigData($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $tablesToDrop = [
            WithConsent::TABLE_NAME,
            DeleteRequest::TABLE_NAME,
            Policy::TABLE_NAME,
            PolicyContent::TABLE_NAME,
            ConsentQueue::TABLE_NAME,
            ActionLog::TABLE_NAME,
            ResourceConsent::TABLE_NAME,
            ResourceConsentStore::TABLE_NAME,
            VisitorConsentLog::TABLE_NAME
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
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE 'amasty_gdpr%'");

        return $this;
    }
}
