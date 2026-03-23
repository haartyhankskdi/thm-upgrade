<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel;

use Amasty\Storelocator\Model\ImageProcessor;
use Magento\Directory\Model\Region;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Gallery extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amlocator_gallery';

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Region
     */
    private $region;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $ioFile;

    public function __construct(
        ImageProcessor $imageProcessor,
        Region $region,
        Filesystem $filesystem,
        File $ioFile,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->imageProcessor = $imageProcessor;
        $this->region = $region;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();

        if (isset($data['image_name']) && $object->isObjectNew()) {
            $this->imageProcessor->processImage(
                $data['image_name'],
                ImageProcessor::GALLERY_IMAGE_TYPE,
                $object->getLocationId(),
                $object->getLocationIsNew()
            );
        }

        return $this;
    }

    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();

        if (isset($data['image_name'])) {
            $this->imageProcessor->setBasePaths(
                ImageProcessor::GALLERY_IMAGE_TYPE,
                $object->getLocationId(),
                $object->getLocationIsNew()
            );
            $this->imageProcessor->deleteImage($data['image_name']);
        }

        return $this;
    }
}
