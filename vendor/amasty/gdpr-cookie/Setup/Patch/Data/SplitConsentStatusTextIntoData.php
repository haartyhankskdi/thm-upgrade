<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class SplitConsentStatusTextIntoData implements DataPatchInterface
{
    private const STATUS_ALL_ALLOWED = 'All Allowed';
    private const STATUS_NONE_ALLOWED = 'None cookies allowed';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState,
        Emulation $appEmulation,
        AreaList $areaList,
        ResolverInterface $localeResolver,
        StoreManagerInterface $storeManager,
        CookieManagementInterface $cookieManagement,
        ResourceInterface $moduleResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->appEmulation = $appEmulation;
        $this->areaList = $areaList;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->cookieManagement = $cookieManagement;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): SplitConsentStatusTextIntoData
    {
        $this->moduleDataSetup->startSetup();
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_GdprCookie');
        if ($setupDataVersion && version_compare($setupDataVersion, '2.8.0', '<')) {
            $this->appState->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'updateModuleData']);
        }
        $this->moduleDataSetup->endSetup();

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

    public function updateModuleData(): void
    {
        $this->prepareCookieConsents();
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteId = (int)$website->getId();
            $storeIds = $this->getStoreIdsByWebsite($websiteId);
            $groupIds = $this->getAvailableGroupIds($storeIds);
            $translStatuses = $this->getTranslatedStatuses($storeIds);
            $this->processAllowedConsentStatus($websiteId, $storeIds, $groupIds, $translStatuses);
            $this->processRejectedConsentStatus($websiteId, $groupIds, $translStatuses);
            $this->processAllAllowedConsentStatus($websiteId, $translStatuses);
            $this->processNoneAllowedConsentStatus($websiteId, $translStatuses);
        }

        $this->processSpecificAllowedConsentStatus();
    }

    public function _resetState(): void
    {
        $this->connection = null;
    }

    private function getConnection(): AdapterInterface
    {
        if (!isset($this->connection)) {
            $this->connection = $this->moduleDataSetup->getConnection();
        }

        return $this->connection;
    }

    private function getConsentTable(): string
    {
        return $this->moduleDataSetup->getTable(CookieConsentResource::TABLE_NAME);
    }

    private function getConsentStatusTable(): string
    {
        return $this->moduleDataSetup->getTable(CookieConsentResource::STATUS_TABLE_NAME);
    }

    private function getStoreIdsByWebsite(int $websiteId): array
    {
        $storeIds = [];
        $stores = $this->storeManager->getWebsite($websiteId)->getStores();
        foreach ($stores as $store) {
            if ($store->getIsActive()) {
                $storeIds[] = (int)$store->getId();
            }
        }

        return $storeIds;
    }

    private function prepareCookieConsents(): void
    {
        $this->getConnection()->update(
            $this->getConsentTable(),
            [ // Convert a list of specific allowed to a comma-separated list.
                CookieConsentInterface::CONSENT_STATUS => new \Zend_Db_Expr(
                    'REPLACE(REPLACE(REPLACE('
                        . $this->getConnection()->quoteIdentifier(CookieConsentInterface::CONSENT_STATUS)
                        . ', \'<br/>\', \',\'), \'<strong>\', \'\'), \':</strong> Allowed\', \'\')'
                )
            ]
        );
    }

    private function processAllowedConsentStatus(
        int $websiteId,
        array $storeIds,
        array $groupIds,
        array $translStatuses
    ): void {
        $select = $this->getGroupSelect($storeIds)
            ->reset(Select::COLUMNS)
            ->columns([
                'cookie_consents_id' => 'c.' . CookieConsentInterface::ID,
                'group_id' => 'g.' . CookieGroupsInterface::ID,
                'status' => new \Zend_Db_Expr(CookieGroupProcessor::CONSENT_STATUS_ACCEPTED)
            ])->joinCross(
                ['c' => $this->getConsentTable()],
                []
            )->where('c.' . CookieConsentInterface::WEBSITE .' = ?', $websiteId)
            ->where('g.' . CookieGroupsInterface::ID . ' IN(?)', $groupIds)
            ->where( // Skip status 'None cookies allowed'
                'c.' . CookieConsentInterface::CONSENT_STATUS . ' NOT IN(?)',
                $translStatuses[self::STATUS_NONE_ALLOWED]
            )->where(
                $this->getConnection()->quoteInto( // Status 'All Allowed'
                    'c.' . CookieConsentInterface::CONSENT_STATUS . ' IN(?)',
                    $translStatuses[self::STATUS_ALL_ALLOWED]
                ) . ' OR (' // Selection by group name for specific allowed, e.g.: 'Essential,Marketing'.
                . $this->getConnection()->getIfNullSql(
                    'gs.is_enabled',
                    'g.' . CookieGroupsInterface::IS_ENABLED
                ) . ' = 1 AND FIND_IN_SET(' . $this->getConnection()->getIfNullSql( // By Store
                    'gs.name',
                    'g.' . CookieGroupsInterface::NAME
                ) . ', c.' . CookieConsentInterface::CONSENT_STATUS . ') OR (' // Default Store
                . 'g.' . CookieGroupsInterface::IS_ENABLED . ' = 1 AND ('
                . 'FIND_IN_SET(g.' . CookieGroupsInterface::NAME
                . ', c.' . CookieConsentInterface::CONSENT_STATUS . '))))'
            );

        $this->fillConsentStatusTable($select);
    }

    private function processRejectedConsentStatus(
        int $websiteId,
        array $groupIds,
        array $translStatuses
    ): void {
        $select = $this->getGroupSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'cookie_consents_id' => 'c.' . CookieConsentInterface::ID,
                'group_id' => 'g.' . CookieGroupsInterface::ID,
                'status' => new \Zend_Db_Expr(CookieGroupProcessor::CONSENT_STATUS_REJECTED)
            ])->joinCross(
                ['c' => $this->getConsentTable()],
                []
            )->joinLeft(
                ['cs' => $this->getConsentStatusTable()],
                'cs.group_id = g.' . CookieGroupsInterface::ID . ' AND cs.cookie_consents_id = c.'
                    . CookieConsentInterface::ID,
                []
            )->where(
                'c.' . CookieConsentInterface::CONSENT_STATUS . ' NOT IN(?)',
                $translStatuses[self::STATUS_ALL_ALLOWED]
            )->where('c.' . CookieConsentInterface::WEBSITE .' = ?', $websiteId)
            ->where('g.' . CookieGroupsInterface::ID . ' IN(?)', $groupIds)
            ->where('g.' . CookieGroupsInterface::IS_ESSENTIAL . ' = 0')
            ->where('cs.group_id IS NULL');

        $this->fillConsentStatusTable($select);
    }

    private function processAllAllowedConsentStatus(int $websiteId, array $translStatuses): void
    {
        $cookieConsentTable = $this->getConsentTable();
        $this->getConnection()->update(
            $cookieConsentTable,
            [CookieConsentInterface::GROUPS_STATUS => CookieConsent::GROUPS_STATUS_ALL_ALLOWED],
            [
                CookieConsentInterface::CONSENT_STATUS . ' IN(?)' => $translStatuses[self::STATUS_ALL_ALLOWED],
                CookieConsentInterface::WEBSITE . ' = ?' => $websiteId
            ]
        );
    }

    private function processNoneAllowedConsentStatus(int $websiteId, array $translStatuses): void
    {
        $cookieConsentTable = $this->getConsentTable();
        $this->getConnection()->update(
            $cookieConsentTable,
            [CookieConsentInterface::GROUPS_STATUS => CookieConsent::GROUPS_STATUS_NONE_ALLOWED],
            [
                CookieConsentInterface::CONSENT_STATUS . ' IN(?)' => $translStatuses[self::STATUS_NONE_ALLOWED],
                CookieConsentInterface::WEBSITE . ' = ?' => $websiteId
            ]
        );
    }

    private function processSpecificAllowedConsentStatus(): void
    {
        $cookieConsentTable = $this->getConsentTable();
        $this->getConnection()->update(
            $cookieConsentTable,
            [CookieConsentInterface::GROUPS_STATUS => CookieConsent::GROUPS_STATUS_SPECIFIC_GROUP],
            [
                CookieConsentInterface::GROUPS_STATUS . ' NOT IN(?)' => [
                    CookieConsent::GROUPS_STATUS_ALL_ALLOWED,
                    CookieConsent::GROUPS_STATUS_NONE_ALLOWED
                ]
            ]
        );
    }

    private function fillConsentStatusTable(Select $select): void
    {
        $query = $this->getConnection()->insertFromSelect(
            $select,
            $this->getConsentStatusTable(),
            ['cookie_consents_id', 'group_id', 'status'],
            AdapterInterface::INSERT_ON_DUPLICATE
        );

        $this->getConnection()->query($query);
    }

    private function getAvailableGroupIds(array $storeIds): array
    {
        $groups = [];
        foreach ($storeIds as $storeId) {
            $groups += $this->cookieManagement->getGroups((int)$storeId);
        }
        ksort($groups);

        return array_keys($groups);
    }

    private function getTranslatedStatuses(array $storeIds): array
    {
        $statuses = [self::STATUS_ALL_ALLOWED, self::STATUS_NONE_ALLOWED];
        $translate = $locales = [];
        foreach ($storeIds as $storeId) {
            $locale = $this->localeResolver->emulate((int)$storeId);
            $this->localeResolver->revert();
            if (!$locale || !in_array($locale, $locales)) {
                $locales[] = $locale;
                $this->appEmulation->startEnvironmentEmulation($storeId);
                $this->areaList->getArea(Area::AREA_FRONTEND)
                    ->load(Area::PART_TRANSLATE);

                foreach ($statuses as $status) {
                    $translate[$status][] = (string)__($status);
                }

                $this->appEmulation->stopEnvironmentEmulation();
            }
        }

        foreach ($statuses as $status) {
            if (isset($translate[$status])) {
                $translate[$status] = array_values(array_unique($translate[$status]));
            } else {
                $translate[$status][] = (string)__($status);
            }
        }

        return $translate;
    }

    private function getGroupSelect(?array $storeIds = null): Select
    {
        $select = $this->getConnection()->select()->from(
            ['g' => $this->moduleDataSetup->getTable(CookieGroupResource::TABLE_NAME)],
            [CookieGroupsInterface::ID]
        )->distinct(true);

        if ($storeIds !== null) {
            $select->joinLeft(
                ['gs' => $this->moduleDataSetup->getTable(CookieGroupResource::STORE_DATA_TABLE_NAME)],
                $this->getConnection()->quoteInto(
                    'gs.group_id = g.' . CookieGroupsInterface::ID . ' AND gs.store_id IN(?)',
                    $storeIds
                ),
                []
            );
        }

        return $select;
    }
}
