<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\Policy as PolicyResource;
use Amasty\Gdpr\Model\ResourceModel\Policy\CollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\Policy\Collection;
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
class PolicyRepository implements PolicyRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    /**
     * @var PolicyResource
     */
    private $policyResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $policies = [];

    /**
     * @var array
     */
    private $currentPolicy = [];

    /**
     * @var CollectionFactory
     */
    private $policyCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        PolicyFactory $policyFactory,
        PolicyResource $policyResource,
        CollectionFactory $policyCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->policyFactory = $policyFactory;
        $this->policyResource = $policyResource;
        $this->policyCollectionFactory = $policyCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(PolicyInterface $policy)
    {
        try {
            if ($policy->getId()) {
                $policy = $this->getById($policy->getId())->addData($policy->getData());
            }
            if ($policy->getStoredData(PolicyInterface::STATUS) == Policy::STATUS_ENABLED) {
                throw new CouldNotSaveException(__('Policy is active'));
            }
            $this->policyResource->save($policy);
            if ($policy->getStatus() == Policy::STATUS_ENABLED) {
                $this->policyResource->disableAllPolicies($policy->getId());
            }
            unset($this->policies[$policy->getId()]);
        } catch (\Exception $e) {
            if ($policy->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save policy with ID %1. Error: %2',
                        [$policy->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new policy. Error: %1', $e->getMessage()));
        }

        return $policy;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->policies[$id])) {
            /** @var \Amasty\Gdpr\Model\Policy $policy */
            $policy = $this->policyFactory->create();
            $this->policyResource->load($policy, $id);
            if (!$policy->getId()) {
                throw new NoSuchEntityException(__('Policy with specified ID "%1" not found.', $id));
            }
            $this->policies[$id] = $policy;
        }

        return $this->policies[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(PolicyInterface $policy)
    {
        try {
            $this->policyResource->delete($policy);
            unset($this->policies[$policy->getId()]);
        } catch (\Exception $e) {
            if ($policy->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove policy with ID %1. Error: %2',
                        [$policy->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove policy. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $policyModel = $this->getById($id);
        $this->delete($policyModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Gdpr\Model\ResourceModel\Policy\Collection $policyCollection */
        $policyCollection = $this->policyCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $policyCollection);
        }
        $searchResults->setTotalCount($policyCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $policyCollection);
        }
        $policyCollection->setCurPage($searchCriteria->getCurrentPage());
        $policyCollection->setPageSize($searchCriteria->getPageSize());
        $policies = [];
        /** @var PolicyInterface $policy */
        foreach ($policyCollection->getItems() as $policy) {
            $policies[] = $this->getById($policy->getId());
        }
        $searchResults->setItems($policies);

        return $searchResults;
    }

    public function _resetState(): void
    {
        $this->policies = [];
        $this->currentPolicy = [];
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $policyCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $policyCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $policyCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $policyCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $policyCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $policyCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    public function getCurrentPolicy($storeId = false)
    {
        if (isset($this->currentPolicy[$storeId])) {
            return $this->currentPolicy[$storeId];
        }

        /** @var \Amasty\Gdpr\Model\ResourceModel\Policy\Collection $policyCollection */
        $policyCollection = $this->policyCollectionFactory->create();

        $policyCollection->addFieldToFilter('status', Policy::STATUS_ENABLED);

        if ($storeId) {
            $policyCollection->joinContent($storeId);
        }

        /** @var Policy $policy */
        $policy = $policyCollection->getFirstItem();

        return $this->currentPolicy[$storeId] = $policy->getId() ? $policy : false;
    }
}
