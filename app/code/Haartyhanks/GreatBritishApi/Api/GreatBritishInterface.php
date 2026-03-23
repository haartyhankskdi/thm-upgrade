<?php
namespace Haartyhanks\GreatBritishApi\Api;

interface GreatBritishInterface
{
    /**
     * @param int|null $categoryId
     * @return array
     */
    public function getGreatBritish($categoryId = null);
}
