<?php

namespace Mw\Extension\Model\Review;

/**
 * Interface ReviewTypeResolverInterface
 */
interface ReviewTypeResolverInterface
{
    /**
     * Resolver Review Type
     *
     * @param \Magento\Review\Model\Review $productReview
     *
     * @return int
     */
    public function getReviewType($productReview): int;
}
