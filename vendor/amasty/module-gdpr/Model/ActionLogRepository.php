<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Amasty\Gdpr\Api\ActionLogRepositoryInterface;
use Amasty\Gdpr\Model\ActionLogFactory;
use Amasty\Gdpr\Model\ResourceModel\ActionLog as ActionLogResource;
use Amasty\Gdpr\Model\ResourceModel\ActionLog\CollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\ActionLog\Collection;
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
class ActionLogRepository implements ActionLogRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ActionLogFactory
     */
    private $actionLogFactory;

    /**
     * @var ActionLogResource
     */
    private $actionLogResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $actionLogs;

    /**
     * @var CollectionFactory
     */
    private $actionLogCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ActionLogFactory $actionLogFactory,
        ActionLogResource $actionLogResource,
        CollectionFactory $actionLogCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->actionLogFactory = $actionLogFactory;
        $this->actionLogResource = $actionLogResource;
        $this->actionLogCollectionFactory = $actionLogCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ActionLogInterface $actionLog)
    {
        try {
            if ($actionLog->getId()) {
                $actionLog = $this->getById($actionLog->getId())->addData($actionLog->getData());
            }
            $this->actionLogResource->save($actionLog);
            unset($this->actionLogs[$actionLog->getId()]);
        } catch (\Exception $e) {
            if ($actionLog->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save actionLog with ID %1. Error: %2',
                        [$actionLog->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new actionLog. Error: %1', $e->getMessage()));
        }

        return $actionLog;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->actionLogs[$id])) {
            /** @var \Amasty\Gdpr\Model\ActionLog $actionLog */
            $actionLog = $this->actionLogFactory->create();
            $this->actionLogResource->load($actionLog, $id);
            if (!$actionLog->getId()) {
                throw new NoSuchEntityException(__('ActionLog with specified ID "%1" not found.', $id));
            }
            $this->actionLogs[$id] = $actionLog;
        }

        return $this->actionLogs[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(ActionLogInterface $actionLog)
    {
        try {
            $this->actionLogResource->delete($actionLog);
            unset($this->actionLogs[$actionLog->getId()]);
        } catch (\Exception $e) {
            if ($actionLog->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove actionLog with ID %1. Error: %2',
                        [$actionLog->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove actionLog. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $actionLogModel = $this->getById($id);
        $this->delete($actionLogModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Gdpr\Model\ResourceModel\ActionLog\Collection $actionLogCollection */
        $actionLogCollection = $this->actionLogCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $actionLogCollection);
        }
        $searchResults->setTotalCount($actionLogCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $actionLogCollection);
        }
        $actionLogCollection->setCurPage($searchCriteria->getCurrentPage());
        $actionLogCollection->setPageSize($searchCriteria->getPageSize());
        $actionLogs = [];
        /** @var ActionLogInterface $actionLog */
        foreach ($actionLogCollection->getItems() as $actionLog) {
            $actionLogs[] = $this->getById($actionLog->getId());
        }
        $searchResults->setItems($actionLogs);

        return $searchResults;
    }

    public function _resetState(): void
    {
        $this->actionLogs = [];
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $actionLogCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $actionLogCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $actionLogCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $actionLogCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $actionLogCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $actionLogCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
