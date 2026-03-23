<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Repository;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory;
use Amasty\Gdpr\Model\WithConsentFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WithConsentRepository implements WithConsentRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var WithConsentFactory
     */
    private $withConsentFactory;

    /**
     * @var WithConsentResource
     */
    private $withConsentResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $withConsents;

    /**
     * @var CollectionFactory
     */
    private $withConsentCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        WithConsentFactory $withConsentFactory,
        WithConsentResource $withConsentResource,
        CollectionFactory $withConsentCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->withConsentFactory = $withConsentFactory;
        $this->withConsentResource = $withConsentResource;
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(WithConsentInterface $withConsent)
    {
        try {
            if ($withConsent->getId()) {
                $withConsent = $this->getById($withConsent->getId())->addData($withConsent->getData());
            }
            $this->withConsentResource->save($withConsent);
            unset($this->withConsents[$withConsent->getId()]);
        } catch (\Exception $e) {
            if ($withConsent->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save withConsent with ID %1. Error: %2',
                        [$withConsent->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new withConsent. Error: %1', $e->getMessage()));
        }

        return $withConsent;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->withConsents[$id])) {
            /** @var \Amasty\Gdpr\Model\WithConsent $withConsent */
            $withConsent = $this->withConsentFactory->create();
            $this->withConsentResource->load($withConsent, $id);
            if (!$withConsent->getId()) {
                throw new NoSuchEntityException(__('WithConsent with specified ID "%1" not found.', $id));
            }
            $this->withConsents[$id] = $withConsent;
        }

        return $this->withConsents[$id];
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $withConsentModel = $this->getById($id);
        $this->delete($withConsentModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(WithConsentInterface $withConsent)
    {
        try {
            $this->withConsentResource->delete($withConsent);
            unset($this->withConsents[$withConsent->getId()]);
        } catch (\Exception $e) {
            if ($withConsent->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove withConsent with ID %1. Error: %2',
                        [$withConsent->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove withConsent. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection $withConsentCollection */
        $withConsentCollection = $this->withConsentCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $withConsentCollection);
        }
        $searchResults->setTotalCount($withConsentCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $withConsentCollection);
        }
        $withConsentCollection->setCurPage($searchCriteria->getCurrentPage());
        $withConsentCollection->setPageSize($searchCriteria->getPageSize());
        $withConsents = [];
        /** @var WithConsentInterface $withConsent */
        foreach ($withConsentCollection->getItems() as $withConsent) {
            $withConsents[] = $this->getById($withConsent->getId());
        }
        $searchResults->setItems($withConsents);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $withConsentCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $withConsentCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $withConsentCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $withConsentCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $withConsentCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $withConsentCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
