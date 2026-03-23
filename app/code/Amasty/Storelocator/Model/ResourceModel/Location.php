<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\DataCollector\Location\CompositeCollector;
use Amasty\Storelocator\Model\GalleryFactory;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Location extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amlocator_location';

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var GalleryFactory
     */
    private $galleryFactory;

    /**
     * @var CompositeCollector|null
     */
    private $compositeCollector;

    public function __construct(
        Context $context,
        ImageProcessor $imageProcessor,
        GalleryCollection $galleryCollection,
        GalleryFactory $galleryFactory,
        Gallery $galleryResource,
        CompositeCollector $compositeCollector,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->compositeCollector = $compositeCollector;
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    /**
     * Perform actions before object save
     * @param AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $scheduleId = (int)$object->getDataByKey('schedule');

        if (!$scheduleId) {
            $object->unsetData('schedule');
        }

        if (($object->getOrigData('marker_img') && $object->getOrigData('marker_img') != $object->getMarkerImg())) {
            $this->imageProcessor->deleteImage($object->getOrigData('marker_img'));
            $object->setMarkerImg($object->getMarkerImg() ? $object->getMarkerImg() : '');
        }

        return $this;
    }

    protected function _beforeDelete(AbstractModel $object)
    {
        //remove location images
        $allImages = $this->galleryCollection->getImagesByLocation($object->getId());

        foreach ($allImages as $image) {
            $this->galleryResource->delete($image);
        }

        //remove location marker
        if ($markerImg = $object->getMarkerImg()) {
            $this->imageProcessor->setBasePaths(
                ImageProcessor::MARKER_IMAGE_TYPE,
                $object->getId(),
                $object->isObjectNew()
            );
            $this->imageProcessor->deleteImage($markerImg);
        }

        return $this;
    }

    protected function _afterSave(AbstractModel $object)
    {
        $data = $object->getData();
        if (isset($data['store_attribute']) && !empty($data['store_attribute'])) {
            $insertData = [];
            $storeId = (int)$object->getId();

            foreach ($data['store_attribute'] as $attributeId => $values) {
                $value = $values;
                if (is_array($values)) {
                    $value = implode(',', $values);
                }
                $insertData[] = [
                    'attribute_id' => $attributeId,
                    'store_id' => $storeId,
                    'value' => $value
                ];
            }

            $table = $this->getTable('amasty_amlocator_store_attribute');

            if (count($insertData) > 0) {
                $this->getConnection()->insertOnDuplicate($table, $insertData, ['value']);
            }
        }
        if ($object->getMarkerImg() && ($image = $object->getData('marker_img'))
            && $object->getOrigData('marker_img') != $object->getMarkerImg()
        ) {
            $this->imageProcessor->processImage(
                $object->getMarkerImg(),
                ImageProcessor::MARKER_IMAGE_TYPE,
                $object->getId(),
                $object->isObjectNew()
            );
        }

        if (!($object->getData('inlineEdit') || $object->getData('massAction'))) {
            $this->saveGallery($object->getData(), $object->isObjectNew());
        }

        $this->_isPkAutoIncrement = true;

        return $this;
    }

    private function saveGallery($data, $isObjectNew = false)
    {
        $locationId = $data['id'];
        $allImages = $this->galleryCollection->getImagesByLocation($locationId);
        $baseImgName = isset($data['base_img']) ? $data['base_img'] : '';

        if (!isset($data['gallery_image'])) {
            foreach ($allImages as $image) {
                $this->galleryResource->delete($image);
            }
            return;
        }
        $galleryImages = $data['gallery_image'];
        $imagesOfLocation = [];
        $isImport = false;

        foreach ($allImages as $image) {
            $imagesOfLocation[$image->getData('image_name')] = $image;
        }

        foreach ($galleryImages as $galleryImage) {
            $isImageNew = isset($galleryImage['tmp_name']);
            if (array_key_exists($galleryImage['name'], $imagesOfLocation)) {
                unset($imagesOfLocation[$galleryImage['name']]);

                if ($isImageNew) {
                    continue;
                }
            }
            if ($isImageNew && isset($galleryImage['name'])) {
                $isImport = true;
                $newImage = $this->galleryFactory->create();
                $newImage->addData(
                    [
                        'location_id' => $locationId,
                        'image_name' => $galleryImage['name'],
                        'is_base' => $baseImgName === $galleryImage['name'],
                        'location_is_new' => $isObjectNew
                    ]
                );
                $this->galleryResource->save($newImage);
            }
        }

        if (!empty($galleryImages) && !$isImport) {
            foreach ($imagesOfLocation as $imageToDelete) {
                $this->galleryResource->delete($imageToDelete);
            }
        }

        $baseImg = $this->galleryCollection->getByNameAndLocation($locationId, $baseImgName);

        if (!empty($baseImg->getData())) {
            foreach ($allImages as $image) {
                if ($image->getData('is_base') == true) {
                    $image->addData(['is_base' => false]);
                    $this->galleryResource->save($image);
                }
            }
            $baseImg->addData(['is_base' => true]);
            $this->galleryResource->save($baseImg);
        }
    }

    /**
     * Set _isPkAutoIncrement for saving new location
     */
    public function setResourceFlags()
    {
        $this->_isPkAutoIncrement = false;
    }

    /**
     * @param string $urlKey
     * @param array $storeIds
     *
     * @return int
     */
    public function matchLocationUrl($urlKey, $storeIds, $onlyEnabled = false)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `stores`)';
        }

        $where = implode(' OR ', $where);
        $select = $this->getConnection()->select()
            ->from(['locations' => $this->getMainTable()])
            ->where('locations.url_key = ?', $urlKey)
            ->where($where)
            ->reset(Select::COLUMNS)
            ->columns('locations.id');

        if ($onlyEnabled) {
            $select->where('locations.'. LocationInterface::STATUS . ' = ?', LocationInterface::STATUS_ENABLED);
        }

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select = $this->joinScheduleTable($select);

        return $select;
    }

    /**
     * Perform actions after object load
     *
     * @param AbstractModel|DataObject $object
     *
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->compositeCollector->collect($object);

        return $this;
    }

    /**
     * Join schedule table
     *
     * @param Select $select
     *
     * @return Select $select
     */
    protected function joinScheduleTable($select)
    {
        $fromPart = $select->getPart(Select::FROM);
        if (isset($fromPart['schedule_table'])) {
            return $select;
        }
        $select->joinLeft(
            ['schedule_table' => $this->getTable(Schedule::TABLE_NAME)],
            $this->getTable(self::TABLE_NAME) . '.schedule = schedule_table.id',
            ['schedule_string' => 'schedule_table.schedule']
        );

        return $select;
    }
}
