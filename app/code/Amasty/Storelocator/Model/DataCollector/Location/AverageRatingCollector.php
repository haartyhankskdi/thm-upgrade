<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Review;

class AverageRatingCollector implements LocationCollectorInterface
{
    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(): void
    {
    }

    public function collect(LocationInterface $location): void
    {
        $rating = 0;
        $count = 0;
        $result = 0;

        if ($reviews = $location->getLocationReviews()) {
            /** @var Review $review */
            foreach ($reviews as $review) {
                $rating += (int)$review->getRating();
                $count++;
            }

            $result = $rating / $count;
        }
        $location->setLocationAverageRating($result);
    }
}
