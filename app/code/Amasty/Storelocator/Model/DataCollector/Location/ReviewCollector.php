<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Data\ReviewInterface;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\ResourceModel\Review;
use Magento\Framework\App\ResourceConnection;

class ReviewCollector implements LocationCollectorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var array
     */
    private $locationReviewsMapping = [];

    public function __construct(
        ResourceConnection $resourceConnection,
        ReviewRepositoryInterface $reviewRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->reviewRepository = $reviewRepository;
    }

    public function initialize(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['rt' => $this->resourceConnection->getTableName(Review::TABLE_NAME)],
            [ReviewInterface::LOCATION_ID]
        );
        $reviews = $this->reviewRepository->getLocationReviews(array_unique($connection->fetchCol($select)));
        foreach ($reviews as $review) {
            $this->locationReviewsMapping[$review->getLocationId()][] = $review;
        }
    }

    public function collect(LocationInterface $location): void
    {
        $result = [];
        if ($location->getId() && array_key_exists($location->getId(), $this->locationReviewsMapping)) {
            $result = $this->locationReviewsMapping[$location->getId()];
        }
        $location->setLocationReviews($result);
    }

    public function _resetState(): void
    {
        $this->locationReviewsMapping = [];
    }
}
