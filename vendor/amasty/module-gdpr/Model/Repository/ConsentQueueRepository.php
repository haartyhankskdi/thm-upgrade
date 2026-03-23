<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Repository;

use Amasty\Gdpr\Api\ConsentQueueRepositoryInterface;
use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ConsentQueueFactory;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue as ConsentQueueResource;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Collection;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class ConsentQueueRepository implements ConsentQueueRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ConsentQueueFactory
     */
    private $consentQueueFactory;

    /**
     * @var ConsentQueueResource
     */
    private $consentQueueResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $consentQueues;

    /**
     * @var CollectionFactory
     */
    private $consentQueueCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ConsentQueueFactory $consentQueueFactory,
        ConsentQueueResource $consentQueueResource,
        CollectionFactory $consentQueueCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->consentQueueFactory = $consentQueueFactory;
        $this->consentQueueResource = $consentQueueResource;
        $this->consentQueueCollectionFactory = $consentQueueCollectionFactory;
    }

    public function save(ConsentQueueInterface $consentQueue): ConsentQueueInterface
    {
        try {
            if ($consentQueue->getId()) {
                $consentQueue = $this->getById((int)$consentQueue->getId())->addData($consentQueue->getData());
            }
            $this->consentQueueResource->save($consentQueue);
            unset($this->consentQueues[$consentQueue->getId()]);
        } catch (\Exception $e) {
            if ($consentQueue->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save consentQueue with ID %1. Error: %2',
                        [$consentQueue->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new consentQueue. Error: %1', $e->getMessage()));
        }

        return $consentQueue;
    }

    public function getById(int $id): ConsentQueueInterface
    {
        if (!isset($this->consentQueues[$id])) {
            /** @var \Amasty\Gdpr\Model\ConsentQueue $consentQueue */
            $consentQueue = $this->consentQueueFactory->create();
            $this->consentQueueResource->load($consentQueue, $id);
            if (!$consentQueue->getId()) {
                throw new NoSuchEntityException(__('ConsentQueue with specified ID "%1" not found.', $id));
            }
            $this->consentQueues[$id] = $consentQueue;
        }

        return $this->consentQueues[$id];
    }

    public function deleteById(int $id): bool
    {
        $consentQueueModel = $this->getById($id);
        $this->delete($consentQueueModel);

        return true;
    }

    public function delete(ConsentQueueInterface $consentQueue): bool
    {
        try {
            $this->consentQueueResource->delete($consentQueue);
            unset($this->consentQueues[$consentQueue->getId()]);
        } catch (\Exception $e) {
            if ($consentQueue->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove consentQueue with ID %1. Error: %2',
                        [$consentQueue->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove consentQueue. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Collection $consentQueueCollection */
        $consentQueueCollection = $this->consentQueueCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $consentQueueCollection);
        }
        $searchResults->setTotalCount($consentQueueCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $consentQueueCollection);
        }
        $consentQueueCollection->setCurPage($searchCriteria->getCurrentPage());
        $consentQueueCollection->setPageSize($searchCriteria->getPageSize());
        $consentQueues = [];
        /** @var ConsentQueueInterface $consentQueue */
        foreach ($consentQueueCollection->getItems() as $consentQueue) {
            $consentQueues[] = $this->getById((int)$consentQueue->getId());
        }
        $searchResults->setItems($consentQueues);

        return $searchResults;
    }

    public function _resetState(): void
    {
        $this->consentQueues = [];
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $consentQueueCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $consentQueueCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $consentQueueCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $consentQueueCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $consentQueueCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $consentQueueCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
