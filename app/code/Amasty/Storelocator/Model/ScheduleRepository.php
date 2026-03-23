<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Api\Data\ScheduleInterface;
use Amasty\Storelocator\Api\Data\ScheduleInterfaceFactory;
use Amasty\Storelocator\Api\Data\ScheduleSearchResultsInterface;
use Amasty\Storelocator\Api\Data\ScheduleSearchResultsInterfaceFactory;
use Amasty\Storelocator\Api\ScheduleRepositoryInterface;
use Amasty\Storelocator\Model\ResourceModel\Schedule as ScheduleResource;
use Amasty\Storelocator\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * @var array
     */
    private $cachedInstances = [];

    /**
     * @var ScheduleInterfaceFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ScheduleSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        ScheduleInterfaceFactory $scheduleFactory,
        ScheduleResource $scheduleResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ScheduleSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleResource = $scheduleResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $scheduleId
     * @param bool $forceReload
     * @return ScheduleInterface
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function get(int $scheduleId, bool $forceReload = false): ScheduleInterface
    {
        if (!$forceReload && isset($this->cachedInstances[$scheduleId])) {
            return $this->cachedInstances[$scheduleId];
        }

        $schedule = $this->scheduleFactory->create();
        $this->scheduleResource->load($schedule, $scheduleId);

        if (!$schedule->getId()) {
            throw new NoSuchEntityException(
                __('Schedule with ID %id does not exist.', ['id' => $scheduleId])
            );
        }

        $this->cachedInstances[$scheduleId] = $schedule;

        return $schedule;
    }

    public function getList(?SearchCriteriaInterface $searchCriteria = null): ScheduleSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        if ($searchCriteria) {
            $searchResults->setSearchCriteria($searchCriteria);
        }

        return $searchResults;
    }

    public function _resetState(): void
    {
        $this->cachedInstances = [];
    }
}
