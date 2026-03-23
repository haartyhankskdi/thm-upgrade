<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Store\Model\StoreManagerInterface;

class LocationContentIndex extends AbstractResource
{
    public const TABLE_NAME = 'amasty_amlocator_content_location_index';
    public const LOCATION_ID = 'location_id';
    public const STORE_LIST_HTML = 'store_list_html';
    public const POPUP_HTML = 'popup_html';
    public const IN = ' IN(?)';
    public const STORE_ID = 'store_id';

    /**
     * @var ResourceConnection
     */
    private $resources;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $tables = [];

    public function __construct(
        ResourceConnection $resources,
        ?StoreManagerInterface $storeManager = null // TODO move to not optional
    ) {
        $this->resources = $resources;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
        parent::__construct();
    }

    protected function _construct(): bool
    {
        return false;
    }

    public function insertData(array $rows, string $tableName = LocationContentIndex::TABLE_NAME): void
    {
        $tableName = $this->getTable($tableName);
        $this->getConnection()->insertMultiple($tableName, $rows);
    }

    public function deleteByIds(array $locationIds = []): void
    {
        $where = [];
        if ($locationIds) {
            $where[] = $this->getConnection()->quoteInto(self::LOCATION_ID . self::IN, $locationIds);
        }

        $this->getConnection()->delete($this->getMainTable(), $where);
    }

    public function getLocationsContent(): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [self::LOCATION_ID, self::STORE_LIST_HTML, self::POPUP_HTML])
            ->where(self::STORE_ID . '=?', [$this->storeManager->getStore()->getId()]);

        return $this->getConnection()->fetchAssoc($select);
    }

    public function getMainTable(): string
    {
        return $this->getTable(self::TABLE_NAME);
    }

    public function getTable(string $tableName): string
    {
        if (!isset($this->tables[$tableName])) {
            $this->tables[$tableName] = $this->resources->getTableName($tableName);
        }

        return $this->tables[$tableName];
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resources->getConnection();
    }

    public function _resetState(): void
    {
        $this->tables = [];
    }
}
