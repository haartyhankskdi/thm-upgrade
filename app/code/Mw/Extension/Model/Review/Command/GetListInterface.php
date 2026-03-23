<?php

declare(strict_types=1);

namespace Mw\Extension\Model\Review\Command;

use Mw\Extension\Api\Data\ReviewSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Find Reviews by SearchCriteria command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial GetList call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Mw\Extension\Api\ReviewRepositoryInterface
 * @api
 */
interface GetListInterface
{
    /**
     * Find Sources by given SearchCriteria. SearchCriteria is not required because load all sources is useful case
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return ReviewSearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): ReviewSearchResultInterface;
}
