<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest as DeleteRequestResource;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteRequestRepository implements DeleteRequestRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var DeleteRequestResource
     */
    private $deleteRequestResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $deleteRequests;

    /**
     * @var CollectionFactory
     */
    private $deleteRequestCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestResource $deleteRequestResource,
        CollectionFactory $deleteRequestCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->deleteRequestResource = $deleteRequestResource;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(DeleteRequestInterface $deleteRequest)
    {
        try {
            if ($deleteRequest->getId()) {
                $deleteRequest = $this->getById($deleteRequest->getId())->addData($deleteRequest->getData());
            }
            $this->deleteRequestResource->save($deleteRequest);
            unset($this->deleteRequests[$deleteRequest->getId()]);
        } catch (\Exception $e) {
            if ($deleteRequest->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save deleteRequest with ID %1. Error: %2',
                        [$deleteRequest->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new deleteRequest. Error: %1', $e->getMessage()));
        }

        return $deleteRequest;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->deleteRequests[$id])) {
            /** @var \Amasty\Gdpr\Model\DeleteRequest $deleteRequest */
            $deleteRequest = $this->deleteRequestFactory->create();
            $this->deleteRequestResource->load($deleteRequest, $id);
            if (!$deleteRequest->getId()) {
                throw new NoSuchEntityException(__('DeleteRequest with specified ID "%1" not found.', $id));
            }
            $this->deleteRequests[$id] = $deleteRequest;
        }

        return $this->deleteRequests[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(DeleteRequestInterface $deleteRequest)
    {
        try {
            $this->deleteRequestResource->delete($deleteRequest);
            unset($this->deleteRequests[$deleteRequest->getId()]);
        } catch (\Exception $e) {
            if ($deleteRequest->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove deleteRequest with ID %1. Error: %2',
                        [$deleteRequest->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove deleteRequest. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $deleteRequestModel = $this->getById($id);
        $this->delete($deleteRequestModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection $deleteRequestCollection */
        $deleteRequestCollection = $this->deleteRequestCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $deleteRequestCollection);
        }
        $searchResults->setTotalCount($deleteRequestCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $deleteRequestCollection);
        }
        $deleteRequestCollection->setCurPage($searchCriteria->getCurrentPage());
        $deleteRequestCollection->setPageSize($searchCriteria->getPageSize());
        $deleteRequests = [];
        /** @var DeleteRequestInterface $deleteRequest */
        foreach ($deleteRequestCollection->getItems() as $deleteRequest) {
            $deleteRequests[] = $this->getById($deleteRequest->getId());
        }
        $searchResults->setItems($deleteRequests);

        return $searchResults;
    }

    public function _resetState(): void
    {
        $this->deleteRequests = [];
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $deleteRequestCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $deleteRequestCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $deleteRequestCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $deleteRequestCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $deleteRequestCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $deleteRequestCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
