<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api;

use Amasty\Storelocator\Api\Data\ScheduleInterface;
use Amasty\Storelocator\Api\Data\ScheduleSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ScheduleRepositoryInterface
{
    /**
     * @param int $scheduleId
     * @param bool $forceReload
     * @throws NoSuchEntityException
     * @return \Amasty\Storelocator\Api\Data\ScheduleInterface
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function get(int $scheduleId, bool $forceReload = false): ScheduleInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Amasty\Storelocator\Api\Data\ScheduleSearchResultsInterface
     */
    public function getList(?SearchCriteriaInterface $searchCriteria = null): ScheduleSearchResultsInterface;
}
