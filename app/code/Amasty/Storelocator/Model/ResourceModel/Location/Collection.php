<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel\Location;

use Amasty\Base\Model\Serializer;
use Amasty\Geoip\Model\Geolocation;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\DataCollector\Location\CompositeCollector;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @method LocationInterface[] getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public const IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA = 'is_need_to_collect_amasty_location_data';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProductValidatorInterface
     */
    private $locationProductValidator;

    /**
     * @var CompositeCollector|null
     */
    private $compositeCollector;

    /**
     * @var LocationProductIndex|null
     */
    private $locationProductIndex;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        ScopeConfigInterface $scope,
        Request $httpRequest,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Serializer $serializer,
        Geolocation $geolocation,
        ConfigProvider $configProvider,
        LocationProductValidatorInterface $locationProductValidator,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null,
        ?CompositeCollector $compositeCollector = null, // TODO move to not optional
        ?LocationProductIndex $locationProductIndex = null // TODO move to not optional
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scope;
        $this->httpRequest = $httpRequest;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->serializer = $serializer;
        $this->geolocation = $geolocation;
        $this->configProvider = $configProvider;
        $this->locationProductValidator = $locationProductValidator;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->compositeCollector = $compositeCollector ?? ObjectManager::getInstance()->get(CompositeCollector::class);
        $this->locationProductIndex =
            $locationProductIndex ?? ObjectManager::getInstance()->get(LocationProductIndex::class);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            Location::class,
            \Amasty\Storelocator\Model\ResourceModel\Location::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Apply filters to locations collection
     *
     * @throws NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        $attributesFromRequest = [];
        $productId = (int)$this->request->getParam('product');
        if (!$productId && $this->coreRegistry->registry('current_product')) {
            $productId = $this->coreRegistry->registry('current_product')->getId();
        }
        $categoryId = (int)$this->request->getParam('category');
        if (!$categoryId && $this->coreRegistry->registry('current_category')) {
            $categoryId = $this->coreRegistry->registry('current_category')->getId();
        }

        if ($productId) {
            $this->filterLocationsByProduct($productId, $store);
        }
        if ($categoryId) {
            $this->filterLocationsByCategory($categoryId, $store);
        }

        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }

        $select->where('main_table.status = 1');
        $this->addDistance($select);

        $params = $this->request->getParams();
        if (isset($params['attributes'])) {
            $attributesFromRequest = $this->prepareRequestParams($params['attributes']);
        }
        $this->applyAttributeFilters($attributesFromRequest);
        $select->order(sprintf('main_table.id %s', Select::SQL_ASC));
    }

    /**
     * Preparing params from request
     *
     * @param array $attributesData
     *
     * @return array $params
     */
    public function prepareRequestParams($attributesData)
    {
        $params = [];

        foreach ($attributesData as $value) {
            // TODO: temporary solution to cover most cases, remove after refactoring
            if ($value['name'] === LocationInterface::CURBSIDE_ENABLED && $value['value'] !== '') {
                $this->addFieldToFilter(
                    LocationInterface::CURBSIDE_ENABLED,
                    (int)$value['value']
                );
                continue;
            }

            if (!empty($value['value']) || $value['value'] != '') {
                $params[(int)$value['name']][] = (int)$value['value'];
            }
        }

        return $params;
    }

    public function load($printQuery = false, $logQuery = false): Collection
    {
        parent::load($printQuery, $logQuery);

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->getFlag(self::IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA)) {
            foreach ($this->_items as $item) {
                $this->compositeCollector->collect($item);
            }
            $this->setFlag(self::IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA, false);
        }

        return $this;
    }

    /**
     * Added distance in select
     *
     * @param Select $select
     *
     * @return Select $select
     */
    public function addDistance($select)
    {
        $lat = (float)$this->request->getPost('lat');
        $lng = (float)$this->request->getPost('lng');
        $sortByDistance = $this->configProvider->getAutomaticLocate()
            || (bool)$this->request->getPost('sortByDistance');
        $radius = (float)$this->request->getPost('radius');

        return $this->addDistanceByParams($lat, $lng, $sortByDistance, $radius, $select);
    }

    public function addDistanceByParams(
        float $lat,
        float $lng,
        bool $sortByDistance,
        float $radius,
        Select $select
    ): Select {
        $ip = $this->httpRequest->getClientIp();

        if ($this->scopeConfig->isSetFlag('amlocator/geoip/use')
            && (!$lat)
        ) {
            $geodata = $this->geolocation->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        if ($lat && $lng && ($sortByDistance || $radius)) {
            if ($radius) {
                $select->having('distance < ' . $radius);
            }

            if ($sortByDistance) {
                $select->order("distance");
            }

            $select->columns(
                [
                    'distance' => 'SQRT(POW(69.1 * (main_table.lat - ' . $lat . '), 2) + '
                    . 'POW(69.1 * (' . $lng . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))'
                ]
            );
        } else {
            $select->order('main_table.position ASC');
            $select->order('main_table.id ASC');
        }

        return $select;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select $countSelect
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Select::COLUMNS);
        $columns = array_merge($select->getPart(Select::COLUMNS), $this->getSelect()->getPart(Select::COLUMNS));
        $select->setPart(Select::COLUMNS, $columns);
        $countSelect = $this->getConnection()->select()
            ->from($select)
            ->reset(Select::COLUMNS)
            ->columns(new \Zend_Db_Expr(("COUNT(*)")));

        return $countSelect;
    }

    /**
     * Apply filters to locations collection
     *
     * @param array $params
     * @return $this
     */
    public function applyAttributeFilters($params)
    {
        if (empty($params)) {
            return $this;
        }
        foreach ($params as $attributeId => $value) {
            $attributeId = (int)$attributeId;
            $this->addConditionsToSelect($attributeId, $value);
        }
        $this->getSelect()->group('main_table.id');

        return $this;
    }

    /**
     * Add conditions
     *
     * @param int $attributeId
     * @param int|array $value
     */
    public function addConditionsToSelect($attributeId, $value)
    {
        $attributeTableAlias = 'store_attribute_' . $attributeId;
        $fromPart = $this->getSelect()->getPart('from');
        if (isset($fromPart[$attributeTableAlias])) {
            return;
        }
        $this->getSelect()
            ->joinLeft(
                [$attributeTableAlias => $this->getTable('amasty_amlocator_store_attribute')],
                "main_table.id = $attributeTableAlias.store_id",
                [
                    $attributeTableAlias . 'value'        => $attributeTableAlias . '.value',
                    $attributeTableAlias . 'attribute_id' => $attributeTableAlias . '.attribute_id'
                ]
            );
        if (is_array($value)) {
            $orWhere = [];
            foreach ($value as $optionId) {
                if (!empty($optionId) || $optionId == '0') {
                    $orWhere[] = "($attributeTableAlias .attribute_id IN ($attributeId)"
                        . " AND FIND_IN_SET(($optionId), $attributeTableAlias.value))";
                }
            }
            if ($orWhere) {
                $this->getSelect()->where(implode(' OR ', $orWhere));
            }
        }
    }

    /**
     * @param array $storeIds
     * @return Select
     */
    public function addFilterByStores($storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `main_table`.`stores`)';
        }

        $where = implode(' OR ', $where);

        return $this->getSelect()->where($where);
    }

    /**
     * Get locations for product
     *
     * @param int|string            $productId
     * @param int|string|null|array $storeIds
     */
    public function filterLocationsByProduct($productId, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $locationIds = $this->locationProductIndex->getLocationIdsByProducts(
            [$productId],
            [Store::DEFAULT_STORE_ID,$storeIds]
        );

        $this->applyFrontendFilter($locationIds);
    }

    /**
     * Get locations for category
     *
     * @param int|string            $categoryId
     * @param int|string|null|array $storeIds
     */
    public function filterLocationsByCategory($categoryId, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $locationIds = $this->locationProductIndex->getLocationIdsByCategories(
            [$categoryId],
            [Store::DEFAULT_STORE_ID, $storeIds]
        );

        $this->applyFrontendFilter($locationIds);
    }

    public function applyFrontendFilter(array $locationIds): void
    {
        $this->_reset();
        $this->setFlag(self::IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA, true);
        $this->addFieldToFilter(
            [
                'main_table.id',
                'main_table.' . LocationInterface::CONDITION_TYPE
            ],
            [
                ['in' => $locationIds],
                ConditionType::NO_CONDITIONS
            ]
        );
    }

    /**
     * Get location data
     *
     * @return array $locationsArray
     */
    public function getLocationData()
    {
        $locationsArray = [];

        $this->joinScheduleTable();

        foreach ($this->getItems() as $location) {
            /** @var Location $location */
            $locationsArray[] = $location->getFrontendData();
        }

        return $locationsArray;
    }

    /**
     * Join schedule table
     *
     * @return $this
     */
    public function joinScheduleTable()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['schedule_table'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['schedule_table' => $this->getTable('amasty_amlocator_schedule')],
            'main_table.schedule = schedule_table.id',
            ['schedule_string' => 'schedule_table.schedule']
        );

        return $this;
    }

    /**
     * Join schedule table
     *
     * @return $this
     */
    public function joinMainImage()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['img'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['img' => $this->getTable(Gallery::TABLE_NAME)],
            'main_table.id = img.location_id AND img.is_base = 1',
            ['main_image_name' => 'img.image_name']
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        return \Magento\Framework\Data\Collection::getAllIds();
    }

    public function getIdsOnPage(): array
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
