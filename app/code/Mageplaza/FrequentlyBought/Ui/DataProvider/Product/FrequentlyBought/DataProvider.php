<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Ui\DataProvider\Product\FrequentlyBought;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought as ResourceModel;

/**
 * Class DataProvider
 * @package Mageplaza\FrequentlyBought\Ui\DataProvider\Product\FrequentlyBought
 */
class DataProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ResourceModel
     */
    protected $frequentlyBoughtResource;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param ResourceModel $frequentlyBoughtResource
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ResourceModel $frequentlyBoughtResource,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->frequentlyBoughtResource = $frequentlyBoughtResource;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');

        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        if (!$this->getProduct()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Add specific filters
     *
     * @param Collection $collection
     *
     * @return Collection
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function addCollectionFilters(Collection $collection)
    {
        $productId = $this->getProduct()->getId();
        if (!$this->frequentlyBoughtResource->hasProductLinks($productId)) {
            return $collection;
        }

        $filter = $this->request->getParam('filters_modifier');
        if (isset($filter['entity_id']['value'])) {
            $collection->addAttributeToFilter(
                $collection->getIdFieldName(),
                ['nin' => $filter['entity_id']['value']]
            );
        }

        return $collection;
    }

    /**
     * Retrieve product
     *
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    protected function getProduct()
    {
        if ($this->product !== null) {
            return $this->product;
        }

        if (!($id = $this->request->getParam('current_product_id'))) {
            return null;
        }

        return $this->product = $this->productRepository->getById($id);
    }

    /**
     * Retrieve store
     *
     * @return StoreInterface|null
     * @throws NoSuchEntityException
     */
    protected function getStore()
    {
        if ($this->store !== null) {
            return $this->store;
        }

        if (!($storeId = $this->request->getParam('current_store_id'))) {
            return null;
        }

        return $this->store = $this->storeRepository->getById($storeId);
    }
}
