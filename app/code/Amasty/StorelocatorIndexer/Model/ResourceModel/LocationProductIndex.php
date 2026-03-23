<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\ResourceModel;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Store\Model\Store;

/**
 * LocationProductIndex for manage location index data
 */
class LocationProductIndex extends AbstractResource
{
    public const TABLE_NAME = 'amasty_amlocator_location_index';
    public const PRODUCT_ID = 'product_id';
    public const LOCATION_ID = 'location_id';
    public const STORE_ID = 'store_id';
    public const IN = ' IN(?)';

    /**
     * @var ResourceConnection
     */
    private $resources;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * Tables used in this resource model
     *
     * @var array
     */
    private $tables = [];

    public function __construct(
        ResourceConnection $resources,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource
    ) {
        $this->resources = $resources;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        parent::__construct();
    }

    protected function _construct()
    {
        return false;
    }

    /**
     * @param array $rows
     * @param string $tableName
     * @throws \Exception
     */
    public function insertData($rows, string $tableName = LocationProductIndex::TABLE_NAME)
    {
        if (!empty($rows)) {
            $tableName = $this->getTable($tableName);
            $this->getConnection()->insertMultiple($tableName, $rows);
        }
    }

    /**
     * @param array|string|null $locationIds
     * @param array|string|null $productIds
     */
    public function deleteByIds($locationIds = null, $productIds = null)
    {
        $where = [];
        if ($locationIds) {
            $where[] = $this->getConnection()->quoteInto(self::LOCATION_ID . self::IN, $locationIds);
        }

        if ($productIds) {
            $where[] = $this->getConnection()->quoteInto(self::PRODUCT_ID . self::IN, $productIds);
        }

        $this->getConnection()->delete($this->getMainTable(), $where);
    }

    /**
     * @deprecated using IndexerTableSwapperInterface
     * @see \Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface
     */
    public function clearIndex()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    /**
     * @param int|array|string $locationIds
     * @param int|array|string $productIds
     * @param int|array|string $storeIds
     * @return bool
     */
    public function validateLocation($locationIds, $productIds, $storeIds)
    {
        $select = $this->getMainTableSelect()
            ->where(self::LOCATION_ID . self::IN, $locationIds)
            ->where(self::PRODUCT_ID . self::IN, $productIds)
            ->where(self::STORE_ID . self::IN, [Store::DEFAULT_STORE_ID, $storeIds]);

        if (!empty($this->getConnection()->fetchOne($select))) {
            return true;
        }

        return false;
    }

    /**
     * @param int|array|string $categoryIds
     * @param int|array|string $storeIds
     * @return array
     */
    public function getLocationsByCategory($categoryIds, $storeIds)
    {
        $productIds = $this->getCategoryProductRelation($categoryIds);

        return $this->getLocationsByProduct($productIds, [Store::DEFAULT_STORE_ID, $storeIds]);
    }

    public function getCategoryProductRelation(array $categoryIds): array
    {
        $select = $this->getConnection()
            ->select()
            ->distinct(true)
            ->from($this->resources->getTableName('catalog_category_product'), ['product_id'])
            ->where($this->getConnection()->prepareSqlCondition('category_id', ['in' => $categoryIds]));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param int|array|string $productIds
     * @param int|array|string $storeIds
     * @return array
     */
    public function getLocationsByProduct($productIds, $storeIds)
    {
        return $this->getConnection()->fetchAll($this->getMainTableSelect()
            ->where(self::PRODUCT_ID . self::IN, $productIds)
            ->where(self::STORE_ID . self::IN, $storeIds));
    }

    public function getLocationIdsByProducts(array $productIds, array $storeIds): array
    {
        return $this->getConnection()->fetchCol($this->getMainTableSelect(LocationProductIndex::LOCATION_ID)
            ->distinct()
            ->where(self::PRODUCT_ID . self::IN, $productIds)
            ->where(self::STORE_ID . self::IN, $storeIds));
    }

    public function getLocationIdsByCategories(array $categoryIds, array $storeIds): array
    {
        $productIds = $this->getCategoryProductRelation($categoryIds);

        return $this->getLocationIdsByProducts($productIds, [Store::DEFAULT_STORE_ID, $storeIds]);
    }

    /**
     * Returns main table name
     * validated by db adapter
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->getTable(self::TABLE_NAME);
    }

    /**
     * Get real table name for db table, validated by db adapter
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $this->tables[$tableName] = $this->resources->getTableName($tableName);
        }

        return $this->tables[$tableName];
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     */
    public function getConnection()
    {
        return $this->resources->getConnection();
    }

    /**
     * @param string $columns
     * @return Select
     */
    private function getMainTableSelect($columns = '*')
    {
        return $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $columns);
    }

    public function _resetState(): void
    {
        $this->tables = [];
    }
}
