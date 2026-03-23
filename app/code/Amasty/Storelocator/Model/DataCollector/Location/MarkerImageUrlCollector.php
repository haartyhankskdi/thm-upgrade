<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ImageProcessor;

class MarkerImageUrlCollector implements LocationCollectorInterface
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

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(): void
    {
    }

    public function collect(LocationInterface $location): void
    {
        if ($location->getMarkerImg()) {
            $location->setMarkerImageUrl(
                $this->imageProcessor->getImageUrl(
                    [ImageProcessor::AMLOCATOR_MEDIA_PATH, $location->getId(), $location->getMarkerImg()]
                )
            );
        }
    }
}
