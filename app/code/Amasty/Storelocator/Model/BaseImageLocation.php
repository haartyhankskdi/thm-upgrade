<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

class BaseImageLocation
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        ImageProcessor $imageProcessor
    ) {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param \Amasty\Storelocator\Model\Location $location
     *
     * @return string
     */
    public function getMainImageUrl($location)
    {
        $baseImage = $location->getMainImageName();

        if ($baseImage) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::AMLOCATOR_GALLERY_MEDIA_PATH, $location->getId(), $baseImage]
            );
        }

        return '';
    }
}
