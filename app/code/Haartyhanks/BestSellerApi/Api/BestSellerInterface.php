<?php
namespace Haartyhanks\BestSellerApi\Api;

interface BestSellerInterface
{
    /**
     * @param int $limit
     * @return array
     */
    public function getBestSellers($limit = 15);
}
