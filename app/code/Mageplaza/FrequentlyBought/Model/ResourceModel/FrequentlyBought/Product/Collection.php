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

namespace Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Product;

use Magento\Catalog\Model\Product;
use Mageplaza\FrequentlyBought\Model\FrequentlyBought;

/**
 * Class Collection
 * @package Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Store product model
     *
     * @var Product
     */
    protected $_product;

    /**
     * Store product link model
     *
     * @var FrequentlyBought
     */
    protected $_fbtModel;

    /**
     * Store strong mode flag that determine if needed for inner join or left join of linked products
     *
     * @var bool
     */
    protected $_isStrongMode;

    /**
     * Store flag that determine if product filter was enabled
     *
     * @var bool
     */
    protected $_hasLinkFilter = false;

    /**
     * Enable strong mode for inner join of linked products
     *
     * @return $this
     */
    public function setIsStrongMode()
    {
        $this->_isStrongMode = true;

        return $this;
    }

    /**
     * Declare link model and initialize type attributes join
     *
     * @param FrequentlyBought $fbtModel
     *
     * @return $this
     */
    public function setFbtModel(FrequentlyBought $fbtModel)
    {
        $this->_fbtModel = $fbtModel;

        return $this;
    }

    /**
     * Retrieve collection fbt model
     *
     * @return FrequentlyBought
     */
    public function getFbtModel()
    {
        return $this->_fbtModel;
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
        if ($product && $product->getId()) {
            $this->_hasLinkFilter = true;
            $this->setStore($product->getStore());
        }

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
     * Exclude products from filter
     *
     * @param array $products
     *
     * @return $this
     */
    public function addExcludeProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $this->_hasLinkFilter = true;
            $this->getSelect()->where('fbt_products.linked_product_id NOT IN (?)', $products);
        }

        return $this;
    }

    /**
     * Add products to filter
     *
     * @param array|int|string $products
     *
     * @return $this
     */
    public function addProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $identifierField = $this->getProductEntityMetadata()->getIdentifierField();
            $this->getSelect()->where("product_entity_table.$identifierField IN (?)", $products);
            $this->_hasLinkFilter = true;
        }

        return $this;
    }

    /**
     * Add random sorting order
     *
     * @return $this
     */
    public function setRandomOrder()
    {
        $this->getSelect()->orderRand('main_table.entity_id');

        return $this;
    }

    /**
     * Setting group by to exclude duplications in collection
     *
     * @param string $groupBy
     *
     * @return $this
     */
    public function setGroupBy($groupBy = 'e.entity_id')
    {
        $this->getSelect()->group($groupBy);

        return $this;
    }

    /**
     * Join linked products when specified link model
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->getFbtModel()) {
            $this->_joinLinks();
            $this->joinProductsToLinks();
        }

        return parent::_beforeLoad();
    }

    /**
     * Join linked products and their attributes
     *
     * @return $this
     */
    protected function _joinLinks()
    {
        $select = $this->getSelect();
        $connection = $select->getConnection();

        $joinCondition = [
            'fbt_products.linked_product_id = e.entity_id'
        ];
        $joinType = 'join';
        $linkField = $this->getProductEntityMetadata()->getLinkField();
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $linkFieldId = $this->getProduct()->getData(
                $linkField
            );
            if ($this->_isStrongMode) {
                $this->getSelect()->where('fbt_products.product_id = ?', (int)$linkFieldId);
            } else {
                $joinType = 'joinLeft';
                $joinCondition[] = $connection->quoteInto('fbt_products.product_id = ?', $linkFieldId);
            }
            $this->addFieldToFilter(
                $linkField,
                ['neq' => $linkFieldId]
            );
        } elseif ($this->_isStrongMode) {
            $this->addFieldToFilter(
                $linkField,
                ['eq' => -1]
            );
        }
        if ($this->_hasLinkFilter) {
            $select->{$joinType}(
                ['fbt_products' => $this->getTable('mageplaza_fbt_product_link')],
                implode(' AND ', $joinCondition),
                ['link_id']
            );
        }

        return $this;
    }

    /**
     * Enable sorting products by its position
     *
     * @param string $dir sort type asc|desc
     *
     * @return $this
     */
    public function setPositionOrder($dir = self::SORT_ORDER_ASC)
    {
        if ($this->_hasLinkFilter) {
            $this->getSelect()->order('fbt_products.position ' . $dir);
        }

        return $this;
    }

    /**
     * Join Product To Links
     * @return void
     */
    private function joinProductsToLinks()
    {
        if ($this->_hasLinkFilter) {
            $metaDataPool = $this->getProductEntityMetadata();
            $linkField = $metaDataPool->getLinkField();
            $entityTable = $metaDataPool->getEntityTable();
            $this->getSelect()
                ->join(
                    ['product_entity_table' => $entityTable],
                    "fbt_products.product_id = product_entity_table.$linkField",
                    []
                );
        }
    }
}
