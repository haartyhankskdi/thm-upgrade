<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Block\View\Schedule;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\BaseImageLocation;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\Geoip\UserLocator;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Location as LocationModel;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class Location extends Template implements BlockInterface, IdentityInterface
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Amasty_Storelocator::center.phtml';

    public const DISTANCE_TYPE_MI = 'mi';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $ioFile;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var AttributeCollection
     */
    protected $attributeCollection;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ConfigProvider
     */
    public $configProvider;

    /**
     * @var Product
     */
    private $productModel;

    /**
     * @var LocationCollection
     */
    private $locationCollection;

    /**
     * @var bool
     */
    private $isLocationCollectionPrepared = false;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;

    /**
     * @var array
     */
    private $attributeIds;

    /**
     * @var BaseImageLocation
     */
    private $baseImageLocation;

    /**
     * @var LocationProductValidatorInterface
     */
    private $locationProductValidator;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var UserLocator
     */
    private $geoipUserLocator;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        File $ioFile,
        Data $dataHelper,
        AttributeCollection $attributeCollection,
        Serializer $serializer,
        ConfigProvider $configProvider,
        ImageProcessor $imageProcessor,
        Product $productModel,
        CollectionFactory $locationCollectionFactory,
        BaseImageLocation $baseImageLocation,
        LocationProductValidatorInterface $locationProductValidator,
        ReviewRepositoryInterface $reviewRepository,
        array $data = [],
        ?UserLocator $geoipUserLocator = null
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->filesystem = $context->getFilesystem();
        $this->jsonEncoder = $jsonEncoder;
        $this->ioFile = $ioFile;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->productModel = $productModel;
        $this->imageProcessor = $imageProcessor;
        $this->baseImageLocation = $baseImageLocation;
        $this->locationProductValidator = $locationProductValidator;
        $this->reviewRepository = $reviewRepository;
        $this->geoipUserLocator = $geoipUserLocator
            ?? ObjectManager::getInstance()->get(UserLocator::class);
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->getNameInLayout() != 'amasty.locator.center'
            && $this->getNameInLayout() != 'amasty.locator.left';
    }

    /**
     * @return string
     */
    public function getLeftBlockHtml()
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Amasty_Storelocator::left.phtml')->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }

    /**
     * @return string
     */
    public function getMainBlockStyles()
    {
        $styles = '';
        if (!$this->isWrap()) {
            $styles = 'clear:both;';
        }

        return $styles;
    }

    /**
     * Get setting for showing store list in widget
     *
     * @return string
     */
    public function getShowLocations()
    {
        if (!$this->hasData('show_locations')) {
            return true; // not widget
        }

        return $this->getData('show_locations');
    }

    /**
     * Get setting for showing search block in widget
     *
     * @return string
     */
    public function getShowSearch()
    {
        if (!$this->hasData('show_search')) {
            return true; // not widget
        }

        return $this->getData('show_search');
    }

    /**
     * Get map wrap style
     *
     * @return bool
     */
    public function isWrap()
    {
        return (bool)$this->getData('wrap_block');
    }

    /**
     * Return map container unic ID
     *
     * @return string
     */
    public function getMapContainerId()
    {
        if (!$this->hasData('map_container_id')) {
            $this->setData('map_container_id', uniqid('amlocator-map-container'));
        }

        return $this->getData('map_container_id');
    }

    /**
     * Return map Element unic ID
     *
     * @return string
     */
    public function getMapId()
    {
        if (!$this->hasData('map_id')) {
            $this->setData('map_id', uniqid('amlocator-map-canvas'));
        }

        return $this->getData('map_id');
    }

    /**
     * Return main image url
     *
     * @param LocationModel $location
     *
     * @return string
     */
    public function getLocationImage($location)
    {
        return $this->baseImageLocation->getMainImageUrl($location);
    }

    /**
     * @return LocationCollection
     */
    public function getLocationCollection()
    {
        if (!$this->isLocationCollectionPrepared) {
            $this->getClearLocationCollection(); // init $this->locationCollection
            $this->locationCollection->joinScheduleTable();
            $this->locationCollection->joinMainImage();

            $this->locationCollection->setFlag(LocationCollection::IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA, true);
            $this->isLocationCollectionPrepared = true;
        }

        return $this->locationCollection;
    }

    public function getClearLocationCollection(): LocationCollection
    {
        if (!$this->locationCollection) {
            $this->locationCollection = $this->locationCollectionFactory->create();
            $this->locationCollection->applyDefaultFilters();

            if ($attributesData = $this->prepareWidgetAttributes()) {
                $this->locationCollection->applyAttributeFilters($attributesData);
            }

            $this->locationCollection->setCurPage((int) $this->getRequest()->getParam('p', 1));
            $this->locationCollection->setPageSize($this->configProvider->getPaginationLimit());
        }

        return $this->locationCollection;
    }

    /**
     * Get attribute ids
     *
     * @return array
     */
    public function getAttributeIds()
    {
        if (!$this->attributeIds) {
            $this->attributeIds = $this->attributeCollection->getAllIds();
        }
        return $this->attributeIds;
    }

    public function prepareWidgetAttributes()
    {
        $params = [];
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, $this->getAttributeIds())) {
                $params[$key] = explode(',', $value);
            }
        }

        return $params;
    }

    public function isOptionSelected($attribute, $option)
    {
        $widgetAttributes = $this->prepareWidgetAttributes();
        if (isset($widgetAttributes[$attribute['attribute_id']])
            && in_array($option['value'], $widgetAttributes[$attribute['attribute_id']])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param LocationCollection $locationCollection
     * @param Product            $product
     *
     * @return bool
     */
    public function isNeedToShowLink($locationCollection, $product)
    {
        foreach ($locationCollection as $location) {
            if ($this->locationProductValidator->isValid($location, $product)) {
                return true;
            }
        }

        return false;
    }

    public function getAmStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        return $store_url;
    }

    /**
     * Get use browser location
     *
     * @return bool
     */
    public function getUseBrowserLocation()
    {
        if (!$this->hasData('usebrowserip')) {
            $this->setData('usebrowserip', $this->configProvider->getUseBrowser());
        }

        return $this->getData('usebrowserip');
    }

    /**
     * Get use geo ip
     *
     * @return bool
     */
    public function getGeoUse()
    {
        if (!$this->hasData('use')) {
            $this->setData('use', $this->configProvider->getUseGeo());
        }

        return $this->getData('use');
    }

    /**
     * Get clustering for map
     *
     * @return bool
     */
    public function getClustering()
    {
        if (!$this->hasData('clustering')) {
            $this->setData('clustering', $this->configProvider->getClustering());
        }

        return $this->getData('clustering');
    }

    /**
     * Get is start search by click on suggestion
     *
     * @return bool
     */
    public function getSuggestionClickSearch()
    {
        if (!$this->hasData('suggestion_click_search')) {
            $this->setData('suggestion_click_search', $this->configProvider->getSuggestionClickSearch());
        }

        return $this->getData('suggestion_click_search');
    }

    /**
     * Get counting distance
     *
     * @return bool
     */
    public function getCountingDistance()
    {
        if (!$this->hasData('count_distance')) {
            $this->setData('count_distance', $this->configProvider->getCountDistance());
        }

        return $this->getData('count_distance');
    }

    /**
     * Get allowed countries
     *
     * @return string
     */
    public function getAllowedCountries()
    {
        $countriesString = $this->configProvider->getAllowedCountries();

        if (!empty($countriesString)) {
            $countriesArray = explode(',', $countriesString);
        } else {
            $countriesArray = [];
        }

        return $this->jsonEncoder->encode($countriesArray);
    }

    public function getJsonLocations()
    {
        $locationArray = [];
        $locationArray['items'] = [];
        /** @var LocationModel $location */
        foreach ($this->getLocationCollection() as $location) {
            if ($markerImg = $location->getMarkerImg()) {
                $location['marker_url'] = $this->imageProcessor->getImageUrl(
                    [ImageProcessor::AMLOCATOR_MEDIA_PATH, $location->getId(), $markerImg]
                );
            }

            $locationArray['items'][] = $location->getFrontendData();
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        //remove double spaces
        $locationArray['block'] = $this->dataHelper->compressHtml($this->getLeftBlockHtml());

        return $this->jsonEncoder->encode($locationArray);
    }

    /**
     * Get zoom for map
     *
     * @return int
     */
    public function getMapZoom()
    {
        if (!$this->hasData('zoom')) {
            $this->setData('zoom', $this->configProvider->getZoom());
        }

        return $this->getData('zoom');
    }

    /**
     * Get filter class
     *
     * @return string|null
     */
    public function getFilterClass()
    {
        if ($this->configProvider->getCollapseFilter()) {
            return ' amlocator-hidden-filter';
        }

        return '';
    }

    /**
     * Get automatic locate nearest location
     *
     * @return bool
     */
    public function getAutomaticLocate()
    {
        if (!$this->hasData('automatic_locate')) {
            $this->setData('automatic_locate', $this->configProvider->getAutomaticLocate());
        }

        return $this->getData('automatic_locate');
    }

    public function getDistanceConfig()
    {
        if (!$this->hasData('distance')) {
            $this->setData('distance', $this->configProvider->getDistanceConfig());
        }

        return $this->getData('distance');
    }

    public function getDistanceLabel()
    {
        $distanceType = $this->getDistanceConfig();

        if ($distanceType === self::DISTANCE_TYPE_MI) {
            return __('mi');
        }

        return __('km');
    }

    public function getRadiusType()
    {
        if (!$this->hasData('radius_type')) {
            $this->setData('radius_type', $this->configProvider->getRadiusType());
        }

        return $this->getData('radius_type');
    }

    public function getMaxRadiusValue()
    {
        if (!$this->hasData('radius_max_value')) {
            $this->setData('radius_max_value', $this->configProvider->getMaxRadiusValue());
        }

        return $this->getData('radius_max_value');
    }

    public function getMinRadiusValue()
    {
        if (!$this->hasData('radius_min_value')) {
            $this->setData('radius_min_value', $this->configProvider->getMinRadiusValue());
        }

        return $this->getData('radius_min_value');
    }

    /**
     * Get radius from config
     *
     * @return array
     */
    public function getSearchRadius()
    {
        if (!$this->hasData('radius')) {
            $this->setData('radius', $this->configProvider->getRadius());
        }

        return explode(',', $this->getData('radius'));
    }

    public function getLinkToMap($params = [])
    {
        return $this->getUrl(
            $this->configProvider->getUrl(),
            ['_query' => $params]
        );
    }

    /**
     * Do not use getUrl method with params due to performance
     *
     * @param int $locationId
     * @return string
     */
    public function getLocationScheduleUrl(int $locationId): string
    {
        return $this->getUrl('amlocator/location/schedule') . "location_id/$locationId/";
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    public function getProduct()
    {
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product');
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category');
        }

        return false;
    }

    public function getProductId()
    {
        if ($this->getRequest()->getParam('product')) {
            return (int)$this->getRequest()->getParam('product');
        }
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product')->getId();
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategoryId()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category')->getId();
        }

        return false;
    }

    public function getProductById($productId)
    {
        $product = $this->productModel->load($productId);

        return $product;
    }

    public function getLinkText()
    {
        if (!$this->hasData('linktext')) {
            $this->setData('linktext', $this->configProvider->getLinkText());
        }

        return $this->getData('linktext');
    }

    public function getTarget()
    {
        $target = '';

        if ($this->configProvider->getOpenNewPage()) {
            $target = 'target="_blank"';
        }

        return $target;
    }

    public function getAttributes()
    {
        return $this->attributeCollection->preparedAttributes();
    }

    public function getLat(): int
    {
        return (int)$this->geoipUserLocator->getLatLng()[UserLocator::KEY_LAT];
    }

    public function getLng(): int
    {
        return (int)$this->geoipUserLocator->getLatLng()[UserLocator::KEY_LNG];
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false
            && strpos($this->getNameInLayout(), 'jsinit') === false
        ) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }

            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }

            $this->getPagerHtml();

            if ($this->pager && !$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                );
            }
            if ($this->pager && $this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Add prev/next pages
     *
     * @param string $url
     * @param array $attributes
     *
     */
    protected function addPrevNext($url, $attributes)
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link_rel',
            ['attributes' => $attributes]
        );
    }

    /**
     * Return Pager for locator page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('amasty.locator.pager')) {
            $this->pager = $this->getLayout()->getBlock('amasty.locator.pager');

            return $this->pager->toHtml();
        }
        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                Pager::class,
                'amasty.locator.pager'
            );

            if ($this->pager) {
                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->configProvider->getPaginationLimit()
                )->setCollection(
                    $this->getClearLocationCollection()
                )->setTemplate(
                    'Amasty_Storelocator::pager.phtml'
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [LocationModel::CACHE_TAG];
    }

    /**
     * @return string[]
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        if ($this->configProvider->getPaginationLimit()) {
            $cacheKeyInfo = array_merge(
                $cacheKeyInfo,
                [implode('-', $this->getClearLocationCollection()->getIdsOnPage())]
            );
        }

        if ($productId = $this->getProductId()) {
            $cacheKeyInfo[] = 'productId_' . $productId;
        }

        return $cacheKeyInfo;
    }

    public function getScheduleHtml(LocationModel $location): string
    {
        if (!$location->getShowSchedule() || empty($location->getSchedule())) {
            return '';
        }
        $scheduleBlock = $this->getChildBlock('amasty_store_locator_schedule');
        if (!$scheduleBlock) {
            $scheduleBlock = $this->addChild('amasty_store_locator_schedule', Schedule::class);
        }
        $scheduleBlock->setData('location', $location);

        return $scheduleBlock->toHtml();
    }

    public function _resetState(): void
    {
        $this->isLocationCollectionPrepared = false;
    }

    public function getUrlAjax(): string
    {
        return $this->getUrl($this->getData('url_ajax') ?? 'amlocator/index/ajax');
    }
}
