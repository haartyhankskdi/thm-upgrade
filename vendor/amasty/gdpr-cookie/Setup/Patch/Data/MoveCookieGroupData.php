<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveCookieGroupData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
    }

    public function apply(): MoveCookieGroupData
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'moveCookieGroupData']);

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

    public function moveCookieGroupData()
    {
        $this->saveCookieGroupData();
        $this->saveCookieGroupStoreData();
    }

    private function saveCookieGroupData()
    {
        $cookieGroupData = $this->collectTableData('amasty_gdprcookie_group_cookie');
        if ($cookieGroupData) {
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable(CookieGroupResource::TABLE_NAME),
                $cookieGroupData
            );
        }
    }

    private function saveCookieGroupStoreData()
    {
        $cookieGroupStoreData = array_merge(
            $this->collectTableData('amasty_gdprcookie_cookie_group_description'), // UpgradeTo210
            $this->collectTableData('amasty_gdprcookie_cookie_group_store') // UpgradeTo240
        );
        if ($cookieGroupStoreData) {
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable(CookieGroupResource::STORE_DATA_TABLE_NAME),
                $cookieGroupStoreData
            );
        }
    }

    private function collectTableData(string $tableName): array
    {
        $tableName = $this->moduleDataSetup->getTable($tableName);
        if ($this->moduleDataSetup->tableExists($tableName)) {
            $connection = $this->moduleDataSetup->getConnection();
            $select = $connection->select()->from($tableName);

            return $connection->fetchAll($select);
        }

        return [];
    }
}
