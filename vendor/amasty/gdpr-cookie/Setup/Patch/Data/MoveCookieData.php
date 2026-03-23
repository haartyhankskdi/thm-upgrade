<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveCookieData implements DataPatchInterface
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

    public function apply(): MoveCookieData
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'moveCookieData']);

        return $this;
    }

    public static function getDependencies()
    {
        return [
            MoveCookieGroupData::class
        ];
    }

    public function getAliases()
    {
        return [];
    }

    public function moveCookieData()
    {
        $this->updateCookieData();
        $this->saveCookieStoreData();
    }

    private function updateCookieData()
    {
        $cookieData = $this->collectCookieData();
        if ($cookieData) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable(CookieResource::TABLE_NAME),
                $cookieData
            );
        }
    }

    private function saveCookieStoreData()
    {
        $cookieStoreData = array_merge(
            $this->collectCookieStoreData('amasty_gdprcookie_cookie_description'), // UpgradeTo210
            $this->collectCookieStoreData('amasty_gdprcookie_cookie_store') // UpgradeTo240
        );
        if ($cookieStoreData) {
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable(CookieResource::STORE_DATA_TABLE_NAME),
                $cookieStoreData
            );
        }
    }

    private function collectCookieData(): array
    {
        $cookieGroupLinkTable = $this->moduleDataSetup->getTable('amasty_gdprcookie_cookie_group_link');
        if ($this->moduleDataSetup->tableExists($cookieGroupLinkTable)) {
            $connection = $this->moduleDataSetup->getConnection();
            $select = $connection->select()
                ->from(['cookie' => $this->moduleDataSetup->getTable(CookieResource::TABLE_NAME)])
                ->joinLeft(
                    ['cookie_group_link' => $cookieGroupLinkTable],
                    'cookie.id = cookie_group_link.cookie_id',
                    ['group_id']
                );

            return $connection->fetchAll($select);
        }

        return [];
    }

    private function collectCookieStoreData(string $tableName): array
    {
        $cookieStoreTable = $this->moduleDataSetup->getTable($tableName);
        if ($this->moduleDataSetup->tableExists($cookieStoreTable)) {
            $connection = $this->moduleDataSetup->getConnection();
            $mapFields = ['cookie_lifetime' => 'lifetime'];
            $fields = [];
            foreach ($connection->describeTable($cookieStoreTable) as $columnName => $columnConfig) {
                $columnName = strtolower($columnName);
                $alias = $mapFields[$columnName] ?? $columnName;
                $fields[$alias] = $columnName;
            }

            $select = $connection->select()
                ->from(['cookie_store' => $cookieStoreTable], $fields);

            $cookieGroupLinkStoreTable = $this->moduleDataSetup->getTable('amasty_gdprcookie_cookie_group_link_store');
            if ($this->moduleDataSetup->tableExists($cookieGroupLinkStoreTable)) {
                $select->joinLeft(
                    ['cookie_group_link_store' => $cookieGroupLinkStoreTable],
                    'cookie_store.cookie_id = cookie_group_link_store.cookie_id'
                    . ' AND cookie_store.store_id = cookie_group_link_store.store_id',
                    ['group_id']
                );
            }

            return $connection->fetchAll($select);
        }

        return [];
    }
}
