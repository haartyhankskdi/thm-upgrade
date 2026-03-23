<?php

declare(strict_types=1);

namespace Mw\Extension\Model\Review;

use Mw\Extension\Api\Data\ReviewInterface;
use Magento\Store\Model\Store;

/**
 * Class ResolveReviewType
 */
class ReviewTypeResolver implements ReviewTypeResolverInterface
{
    /**
     * Retrieve review type
     *
     * @param \Magento\Review\Model\Review $productReview
     *
     * @return int
     */
    public function getReviewType($productReview): int
    {
        $customerId = $productReview->getCustomerId();

        if ($customerId) {
            return ReviewInterface::REVIEW_TYPE_CUSTOMER;
        }

        $storeId = (int)$productReview->getStoreId();

        if ($storeId === Store::DEFAULT_STORE_ID) {
            return ReviewInterface::REVIEW_TYPE_ADMIN;
        }

        return ReviewInterface::REVIEW_TYPE_GUEST;
    }
}
