<?php

declare(strict_types=1);

namespace Mw\Extension\Api;

/**
 * Retrieve product reviews by sku
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface GetProductReviewsInterface
{
    /**
     * Get product reviews.
     *
     * @param string $sku
     * @return \Mw\Extension\Api\Data\ReviewInterface[]
     */
    public function execute(string $sku);
}
