<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Block\View\ReviewsFactory;

class RatingHtmlCollector implements LocationCollectorInterface
{
    /**
     * @var ReviewsFactory
     */
    private $reviewsFactory;

    public function __construct(
        ReviewsFactory $reviewsFactory
    ) {
        $this->reviewsFactory = $reviewsFactory;
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(): void
    {
    }

    public function collect(LocationInterface $location): void
    {
        $result = $this->reviewsFactory->create()
            ->setData('location', $location)
            ->setTemplate('Amasty_Storelocator::rating.phtml')
            ->toHtml();
        $location->setRating($result);
    }
}
