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

namespace Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\FrequentlyBought\Model\FrequentlyBought;

/**
 * Class Collection
 * @package Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought
 */
class Collection extends AbstractCollection
{
    /**
     * Product object
     *
     * @var Product
     */
    protected $_product;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            FrequentlyBought::class,
            \Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought::class
        );
    }

    /**
     * Initialize collection parent product and add limitation join
     *
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->_product = $product;

        return $this;
    }

    /**
     * Retrieve collection base product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Add product to filter
     *
     * @return $this
     */
    public function addProductIdFilter()
    {
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $this->addFieldToFilter('product_id', ['eq' => $this->getProduct()->getId()]);
        }

        return $this;
    }
}
