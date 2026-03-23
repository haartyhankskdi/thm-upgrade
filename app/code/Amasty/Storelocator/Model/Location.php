<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Api\Data\LocationExtensionInterface;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Data\ReviewInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CatalogRuleCombine;
use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as SalesRuleCombine;
use Magento\Widget\Model\Template\Filter;

/**
 * Class Location
 *
 * Define location and actions with it
 */
class Location extends \Magento\Rule\Model\AbstractModel implements LocationInterface
{
    public const CACHE_TAG = 'amlocator_location';
    public const EVENT_PREFIX = 'amasty_storelocator_location';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = self::EVENT_PREFIX;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Storelocator\Model\ResourceModel\Location::class);
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getProductConditions()
    {
        $conditionsObject = $this->getActions();
        $conditions = $conditionsObject->getConditions();
        $productCondition = [];
        foreach ($conditions as $condition) {
            if ($condition['form_name'] == 'catalog_rule_form') {
                $productCondition[] = $condition;
            }
        }
        $conditionsObject->setConditions($productCondition);

        return $conditionsObject;
    }

    /**
     * @return ReviewInterface[]|null
     */
    public function getLocationReviews(): ?array
    {
        return $this->getData(self::REVIEWS);
    }

    /**
     * @param ReviewInterface[]|null $reviews
     * @return void
     */
    public function setLocationReviews(?array $reviews): void
    {
        $this->setData(self::REVIEWS, $reviews);
    }

    /**
     * @return ?string
     */
    public function getRating(): ?string
    {
        return $this->getData(self::RATING);
    }

    /**
     * @param null|string $rating
     * @return void
     */
    public function setRating(?string $rating): void
    {
        $this->setData(self::RATING, $rating);
    }

    /**
     * @return null|string
     */
    public function getWorkingTimeToday(): ?string
    {
        return $this->getData(self::WORKING_TIME_TODAY);
    }

    /**
     * @param null|string $workingTimeToday
     * @return void
     */
    public function setWorkingTimeToday(?string $workingTimeToday): void
    {
        $this->setData(self::WORKING_TIME_TODAY, $workingTimeToday);
    }

    /**
     * @return float
     */
    public function getLocationAverageRating(): float
    {
        return $this->getData(self::AVERAGE_RATING);
    }

    /**
     * @param float $averageRating
     * @return void
     */
    public function setLocationAverageRating(float $averageRating): void
    {
        $this->setData(self::AVERAGE_RATING, $averageRating);
    }

    /**
     * @return array|null
     */
    public function getAttributes(): ?array
    {
        return $this->getData(self::ATTRIBUTES);
    }

    /**
     * @param array|null $attributes
     * @return void
     */
    public function setAttributes(?array $attributes): void
    {
        $this->setData(self::ATTRIBUTES, $attributes);
    }

    public function activate()
    {
        $this->setStatus(1);
        $this->setData('massAction', true);
        $this->save();

        return $this;
    }

    public function inactivate()
    {
        $this->setStatus(0);
        $this->setData('massAction', true);
        $this->save();

        return $this;
    }

    /**
     * Set flags for saving new location
     */
    public function setModelFlags()
    {
        $this->getResource()->setResourceFlags();
    }

    /**
     * Optimized get data method
     *
     * @return array
     */
    public function getFrontendData(): array
    {
        $result = [
            'id' => (int)$this->getDataByKey('id'),
            'name' => $this->getDataByKey('name'),
            'lat' => $this->getDataByKey('lat'),
            'lng' => $this->getDataByKey('lng'),
            'popup_html' => $this->getDataByKey('popup_html')
        ];

        if ($this->getDataByKey('marker_url')) {
            $result['marker_url'] = $this->getDataByKey('marker_url');
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->setData(self::COUNTRY, $country);
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->getData(self::ZIP);
    }

    /**
     * @param string|null $zip
     */
    public function setZip(?string $zip): void
    {
        $this->setData(self::ZIP, $zip);
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->getData(self::LAT);
    }

    /**
     * @param string|null $lat
     */
    public function setLat(?string $lat): void
    {
        $this->setData(self::LAT, $lat);
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->getData(self::LNG);
    }

    /**
     * @param string|null $lng
     */
    public function setLng(?string $lng): void
    {
        $this->setData(self::LNG, $lng);
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->getData(self::PHOTO);
    }

    /**
     * @param string|null $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->setData(self::PHOTO, $photo);
    }

    /**
     * @return string|null
     */
    public function getMarker(): ?string
    {
        return $this->getData(self::MARKER);
    }

    /**
     * @param string|null $marker
     */
    public function setMarker(?string $marker): void
    {
        $this->setData(self::MARKER, $marker);
    }

    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @param string|null $position
     */
    public function setPosition(?string $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->getData(self::STATE);
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->setData(self::STATE, $state);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        $description = $this->getData(self::DESCRIPTION);

        if ($description) {
            //use object manager to avoid loading dependencies of parent class
            $filter = ObjectManager::getInstance()->get(Filter::class);
            $description = $filter->filter((string)$description);
        }

        return $description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->getData(self::WEBSITE);
    }

    /**
     * @param string|null $website
     */
    public function setWebsite(?string $website): void
    {
        $this->setData(self::WEBSITE, $website);
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category): void
    {
        $this->setData(self::CATEGORY, $category);
    }

    /**
     * @return string|null
     */
    public function getActionsSerialized(): ?string
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * @param string|null $actionsSerialized
     */
    public function setActionsSerialized(?string $actionsSerialized): void
    {
        $this->setData(self::ACTIONS_SERIALIZED, $actionsSerialized);
    }

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string
    {
        return $this->getData(self::STORE_IMG);
    }

    /**
     * @param string|null $storeImg
     */
    public function setStoreImg(?string $storeImg): void
    {
        $this->setData(self::STORE_IMG, $storeImg);
    }

    /**
     * @return string|null
     */
    public function getStores(): ?string
    {
        return $this->getData(self::STORES);
    }

    /**
     * @param string|null $stores
     */
    public function setStores(?string $stores): void
    {
        $this->setData(self::STORES, $stores);
    }

    /**
     * @return string|null
     */
    public function getSchedule(): ?string
    {
        return $this->getData(self::SCHEDULE);
    }

    /**
     * @param string|null $schedule
     */
    public function setSchedule(?string $schedule): void
    {
        $this->setData(self::SCHEDULE, $schedule);
    }

    /**
     * @return string|null
     */
    public function getMarkerImg(): ?string
    {
        return $this->getData(self::MARKER_IMG);
    }

    /**
     * @param string|null $markerImg
     */
    public function setMarkerImg(?string $markerImg): void
    {
        $this->setData(self::MARKER_IMG, $markerImg);
    }

    /**
     * @return string|null
     */
    public function getMarkerImageUrl(): ?string
    {
        return $this->getData(self::MARKER_URL);
    }

    /**
     * @param string|null $markerImageUrl
     */
    public function setMarkerImageUrl(?string $markerImageUrl): void
    {
        $this->setData(self::MARKER_URL, $markerImageUrl);
    }

    /**
     * @return string|null
     */
    public function getShowSchedule(): ?string
    {
        return $this->getData(self::SHOW_SCHEDULE);
    }

    /**
     * @param string|null $showSchedule
     */
    public function setShowSchedule(?string $showSchedule): void
    {
        $this->setData(self::SHOW_SCHEDULE, $showSchedule);
    }

    /**
     * @return string|null
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @param string|null $urlKey
     */
    public function setUrlKey(?string $urlKey): void
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @return string|null
     */
    public function getMetaRobots(): ?string
    {
        return $this->getData(self::META_ROBOTS);
    }

    /**
     * @param string|null $metaRobots
     */
    public function setMetaRobots(?string $metaRobots): void
    {
        $this->setData(self::META_ROBOTS, $metaRobots);
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->getData(self::CANONICAL_URL);
    }

    /**
     * @param string|null $canonicalUrl
     */
    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return int
     */
    public function getConditionType(): int
    {
        return (int)$this->getData(self::CONDITION_TYPE);
    }

    /**
     * @param int $conditionType
     */
    public function setConditionType(int $conditionType): void
    {
        $this->setData(self::CONDITION_TYPE, $conditionType);
    }

    /**
     * @return bool
     */
    public function getCurbsideEnabled(): bool
    {
        return (bool)$this->getData(self::CURBSIDE_ENABLED);
    }

    /**
     * @param bool $curbsideEnabled
     * @return void
     */
    public function setCurbsideEnabled(bool $curbsideEnabled): void
    {
        $this->setData(self::CURBSIDE_ENABLED, $curbsideEnabled);
    }

    /**
     * @return string|null
     */
    public function getCurbsideConditionsText(): ?string
    {
        return $this->getData(self::CURBSIDE_CONDITIONS_TEXT);
    }

    /**
     * @param string|null $curbsideConditionsText
     * @return void
     */
    public function setCurbsideConditionsText(?string $curbsideConditionsText): void
    {
        $this->setData(self::CURBSIDE_CONDITIONS_TEXT, $curbsideConditionsText);
    }

    public function getConditionsInstance()
    {
        return ObjectManager::getInstance()->create(SalesRuleCombine::class);
    }

    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(CatalogRuleCombine::class);
    }

    public function getExtensionAttributes(): LocationExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(LocationExtensionInterface $extensionAttributes): LocationInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
