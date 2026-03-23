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

namespace Mageplaza\FrequentlyBought\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Collection as FrequentlyBoughtCollection;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\CollectionFactory as FrequentlyBoughtCollectionFactory;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Product\Collection as ProductCollection;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class FrequentlyBought
 * @package Mageplaza\FrequentlyBought\Model
 */
class FrequentlyBought extends AbstractModel implements ProductLinkInterface
{
    const KEY_SKU = 'sku';
    const KEY_LINKED_PRODUCT_SKU = 'linked_product_sku';
    const KEY_LINKED_PRODUCT_TYPE = 'linked_product_type';
    const KEY_POSITION = 'position';

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    protected $_fbtCollectionFactory;

    /**
     * FrequentlyBought constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductCollectionFactory $collectionFactory
     * @param FrequentlyBoughtCollectionFactory $fbtCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductCollectionFactory $collectionFactory,
        FrequentlyBoughtCollectionFactory $fbtCollectionFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productCollectionFactory = $collectionFactory;
        $this->_fbtCollectionFactory = $fbtCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\FrequentlyBought::class);
    }

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    protected function _get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Return Data Object data in array format.
     *
     * @return array
     */
    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Retrieve linked product collection
     *
     * @param Product $product
     *
     * @return ProductCollection
     */
    public function getProductCollection(Product $product)
    {
        /** @var ProductCollection $collection */
        $collection = $this->_productCollectionFactory->create()->setFbtModel($this);
        $collection->setIsStrongMode()->setProduct($product);

        return $collection;
    }

    /**
     * Retrieve link collection
     *
     * @return FrequentlyBoughtCollection
     */
    public function getFbtCollection()
    {
        return $this->_fbtCollectionFactory->create();
    }

    /**
     * Get SKU
     *
     * @identifier
     * @return string
     */
    public function getSku()
    {
        return $this->_get(self::KEY_SKU);
    }

    /**
     * Get linked product sku
     *
     * @identifier
     * @return string
     */
    public function getLinkedProductSku()
    {
        return $this->_get(self::KEY_LINKED_PRODUCT_SKU);
    }

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     */
    public function getLinkedProductType()
    {
        return $this->_get(self::KEY_LINKED_PRODUCT_TYPE);
    }

    /**
     * Get product position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_get(self::KEY_POSITION);
    }

    /**
     * Set SKU
     *
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     *
     * @return $this
     */
    public function setLinkedProductSku($linkedProductSku)
    {
        return $this->setData(self::KEY_LINKED_PRODUCT_SKU, $linkedProductSku);
    }

    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     *
     * @return $this
     */
    public function setLinkedProductType($linkedProductType)
    {
        return $this->setData(self::KEY_LINKED_PRODUCT_TYPE, $linkedProductType);
    }

    /**
     * Set linked item position
     *
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }
}
