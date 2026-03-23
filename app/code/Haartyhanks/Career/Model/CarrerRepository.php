<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Haartyhanks\Career\Api\CarrerRepositoryInterface;
use Haartyhanks\Career\Api\Data\CarrerInterfaceFactory;
use Haartyhanks\Career\Api\Data\CarrerSearchResultsInterfaceFactory;
use Haartyhanks\Career\Model\ResourceModel\Carrer as ResourceCarrer;
use Haartyhanks\Career\Model\ResourceModel\Carrer\CollectionFactory as CarrerCollectionFactory;

class CarrerRepository implements CarrerRepositoryInterface
{

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $dataCarrerFactory;

    protected $searchResultsFactory;

    protected $carrerCollectionFactory;

    private $storeManager;

    protected $dataObjectHelper;

    protected $carrerFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;


    /**
     * @param ResourceCarrer $resource
     * @param CarrerFactory $carrerFactory
     * @param CarrerInterfaceFactory $dataCarrerFactory
     * @param CarrerCollectionFactory $carrerCollectionFactory
     * @param CarrerSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCarrer $resource,
        CarrerFactory $carrerFactory,
        CarrerInterfaceFactory $dataCarrerFactory,
        CarrerCollectionFactory $carrerCollectionFactory,
        CarrerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->carrerFactory = $carrerFactory;
        $this->carrerCollectionFactory = $carrerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCarrerFactory = $dataCarrerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
    ) {
        /* if (empty($carrer->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $carrer->setStoreId($storeId);
        } */
        
        $carrerData = $this->extensibleDataObjectConverter->toNestedArray(
            $carrer,
            [],
            \Haartyhanks\Career\Api\Data\CarrerInterface::class
        );
        
        $carrerModel = $this->carrerFactory->create()->setData($carrerData);
        
        try {
            $this->resource->save($carrerModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the carrer: %1',
                $exception->getMessage()
            ));
        }
        return $carrerModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($carrerId)
    {
        $carrer = $this->carrerFactory->create();
        $this->resource->load($carrer, $carrerId);
        if (!$carrer->getId()) {
            throw new NoSuchEntityException(__('Carrer with id "%1" does not exist.', $carrerId));
        }
        return $carrer->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->carrerCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Haartyhanks\Career\Api\Data\CarrerInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
    ) {
        try {
            $carrerModel = $this->carrerFactory->create();
            $this->resource->load($carrerModel, $carrer->getCarrerId());
            $this->resource->delete($carrerModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Carrer: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($carrerId)
    {
        return $this->delete($this->get($carrerId));
    }
}

