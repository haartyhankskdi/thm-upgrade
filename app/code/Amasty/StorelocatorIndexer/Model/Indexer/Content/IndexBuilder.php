<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Indexer for Magento 2 (System)
 */

namespace Amasty\StorelocatorIndexer\Model\Indexer\Content;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ConfigHtmlConverter;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Amasty\StorelocatorIndexer\Model\Indexer\AbstractIndexBuilder;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationContentIndex;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class IndexBuilder extends AbstractIndexBuilder
{
    public const INDEXER_ID = 'amasty_store_locator_content_indexer';

    /**
     * @var ConfigHtmlConverter
     */
    private $configHtmlConverter;

    /**
     * @var LocationContentIndex
     */
    private $contentIndex;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int[]
     */
    private $allStoreIds = [];

    public function __construct(
        LocationCollectionFactory $locationCollectionFactory,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        ConfigHtmlConverter $configHtmlConverter,
        LocationContentIndex $contentIndex,
        TableSwapper $tableSwapper,
        $batchSize = 1000,
        ?Emulation $emulation = null, // TODO move to not optional
        ?State $appState = null, // TODO move to not optional
        ?StoreManagerInterface $storeManager = null // TODO move to not optional
    ) {
        parent::__construct($locationCollectionFactory, $logger, $productCollectionFactory, $batchSize);
        $this->configHtmlConverter = $configHtmlConverter;
        $this->contentIndex = $contentIndex;
        $this->tableSwapper = $tableSwapper;
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->appState = $appState ?? ObjectManager::getInstance()->get(State::class);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    protected function doReindexByIds($ids)
    {
        $this->contentIndex->deleteByIds($ids);
        $locations = $this->locationCollectionFactory->create();
        $locations->joinMainImage();
        $locations->addFieldToFilter('main_table.' . LocationInterface::ID, ['in' => $ids]);
        $this->updateIndexData($locations->getItems());
    }

    protected function doReindexFull()
    {
        $tableName = $this->tableSwapper->getWorkingTableName($this->contentIndex->getMainTable());
        // unable to use getAllLocations, because we need all locations
        $locations = $this->locationCollectionFactory->create();
        $locations->joinMainImage();

        $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this, 'updateIndexData'],
            [$locations->getItems(), $tableName]
        );

        $this->tableSwapper->swapIndexTables([$this->contentIndex->getMainTable()]);
    }

    public function updateIndexData(array $locations, string $tableName = LocationContentIndex::TABLE_NAME)
    {
        $insertData = [];
        $count = 0;
        foreach ($locations as $location) {
            $stores = explode(',', $location->getStores());
            // For 'All Store Views' reindex for each store
            if (in_array(Store::DEFAULT_STORE_ID, $stores, false)) {
                $stores = $this->getAllStoreIds();
            }
            foreach ($stores as $storeId) {
                if (empty($storeId)) {
                    continue;
                }

                $this->emulation->startEnvironmentEmulation((int) $storeId, Area::AREA_FRONTEND, true);
                $location->unsetData('prepared_name');
                $this->configHtmlConverter->setHtml($location);
                $this->emulation->stopEnvironmentEmulation();

                $insertData[] = [
                    LocationContentIndex::LOCATION_ID => $location->getId(),
                    LocationContentIndex::STORE_LIST_HTML => $location->getStoreListHtml(),
                    LocationContentIndex::POPUP_HTML => $location->getPopupHtml(),
                    Store::STORE_ID => (int) $storeId
                ];

            }

            if (++$count == $this->batchSize) {
                $this->contentIndex->insertData($insertData, $tableName);
                $insertData = [];
                $count = 0;
            }
        }

        if (!empty($insertData)) {
            $this->contentIndex->insertData($insertData, $tableName);
        }
    }

    private function getAllStoreIds(): array
    {
        if (empty($this->allStoreIds)) {
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                $this->allStoreIds[] = (int)$store->getId();
            }
        }

        return $this->allStoreIds;
    }

    public function _resetState(): void
    {
        $this->allStoreIds = [];
    }
}
